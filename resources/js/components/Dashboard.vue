<template>
    <div class="dashboard-container">
        <Toast />
        
        <!-- Loading Overlay -->
        <div v-if="loading" class="loading-overlay">
            <ProgressSpinner />
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card>
                <template #content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">{{ $t('dashboard.stats.totalBookings') }}</p>
                            <p class="text-3xl font-bold text-blue-600">{{ stats.totalBookings }}</p>
                        </div>
                        <i class="pi pi-calendar text-4xl text-blue-400"></i>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">{{ $t('dashboard.stats.activeTables') }}</p>
                            <p class="text-3xl font-bold text-green-600">{{ stats.activeTables }}</p>
                        </div>
                        <i class="pi pi-table text-4xl text-green-400"></i>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">{{ $t('dashboard.stats.totalPlaces') }}</p>
                            <p class="text-3xl font-bold text-purple-600">{{ stats.totalPlaces }}</p>
                        </div>
                        <i class="pi pi-map-marker text-4xl text-purple-400"></i>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">{{ $t('dashboard.stats.pendingOrders') }}</p>
                            <p class="text-3xl font-bold text-orange-600">{{ stats.pendingOrders }}</p>
                        </div>
                        <i class="pi pi-shopping-cart text-4xl text-orange-400"></i>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Recent Bookings Table -->
            <Card class="lg:col-span-2">
                <template #title>
                    <div class="flex justify-between items-center">
                        <span>{{ $t('dashboard.recentBookings.title') }}</span>
                        <Button :label="$t('dashboard.recentBookings.viewAll')" size="small" text />
                    </div>
                </template>
                <template #content>
                    <DataTable 
                        :value="recentBookings" 
                        stripedRows 
                        :paginator="true" 
                        :rows="5"
                        class="p-datatable-sm"
                    >
                        <Column field="id" :header="$t('dashboard.recentBookings.columns.id')" sortable></Column>
                        <Column field="customer" :header="$t('dashboard.recentBookings.columns.customer')" sortable></Column>
                        <Column field="table" :header="$t('dashboard.recentBookings.columns.table')" sortable></Column>
                        <Column field="date" :header="$t('dashboard.recentBookings.columns.date')" sortable></Column>
                        <Column field="time" :header="$t('dashboard.recentBookings.columns.time')" sortable></Column>
                        <Column field="status" :header="$t('dashboard.recentBookings.columns.status')">
                            <template #body="slotProps">
                                <span 
                                    :class="getStatusClass(slotProps.data.status)"
                                    class="px-2 py-1 rounded text-xs font-semibold"
                                >
                                    {{ translateStatus(slotProps.data.status) }}
                                </span>
                            </template>
                        </Column>
                        <Column :header="$t('common.actions')">
                            <template #body>
                                <div class="flex gap-2">
                                    <Button icon="pi pi-eye" size="small" text rounded />
                                    <Button icon="pi pi-pencil" size="small" text rounded severity="info" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>

            <!-- Quick Actions & Chart -->
            <div class="space-y-4">
                <!-- Quick Actions -->
                <Card>
                    <template #title>{{ $t('dashboard.quickActions.title') }}</template>
                    <template #content>
                        <div class="flex flex-col gap-2">
                            <Button :label="$t('dashboard.quickActions.newBooking')" icon="pi pi-plus" severity="success" class="w-full" />
                            <Button :label="$t('dashboard.quickActions.manageTables')" icon="pi pi-table" severity="info" class="w-full" />
                            <Button :label="$t('dashboard.quickActions.viewMenu')" icon="pi pi-list" severity="secondary" class="w-full" />
                            <Button :label="$t('common.settings')" icon="pi pi-cog" severity="secondary" class="w-full" />
                        </div>
                    </template>
                </Card>

                <!-- Chart -->
                <Card>
                    <template #title>{{ $t('dashboard.weeklyOverview.title') }}</template>
                    <template #content>
                        <Chart type="line" :data="chartData" :options="chartOptions" class="h-64" />
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const toast = useToast();
const { t } = useI18n();

const loading = ref(true);
const dashboardData = ref(null);

const translateStatus = (status) => {
    const statusMap = {
        'confirmed': t('dashboard.recentBookings.status.confirmed'),
        'pending': t('dashboard.recentBookings.status.pending'),
        'cancelled': t('dashboard.recentBookings.status.cancelled'),
        'completed': t('dashboard.recentBookings.status.completed'),
        'paid': t('dashboard.recentBookings.status.confirmed')
    };
    return statusMap[status?.toLowerCase()] || status;
};

// Computed properties from API data
const stats = computed(() => {
    if (!dashboardData.value) {
        return {
            totalBookings: 0,
            activeTables: 0,
            totalPlaces: 0,
            pendingOrders: 0
        };
    }

    const todayStats = dashboardData.value.today_stats || {};
    const tables = dashboardData.value.tables || {};
    const places = dashboardData.value.places || {};

    return {
        totalBookings: todayStats.total_reservations || 0,
        activeTables: `${tables.active || 0}/${tables.total || 0}`,
        totalPlaces: places.total || 0,
        pendingOrders: todayStats.pending || 0
    };
});

const recentBookings = computed(() => {
    if (!dashboardData.value?.recent_reservations) {
        return [];
    }

    return dashboardData.value.recent_reservations.map(reservation => ({
        id: reservation.id,
        customer: reservation.customer_name || reservation.name || '-',
        table: reservation.table?.name || reservation.table_name || '-',
        date: new Date(reservation.date || reservation.reservation_date).toLocaleDateString('ka-GE'),
        time: reservation.time || reservation.time_from || '-',
        status: reservation.status
    }));
});

const chartData = ref({
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    datasets: [
        {
            label: t('dashboard.weeklyOverview.bookings'),
            data: [12, 19, 15, 25, 22, 30, 28],
            fill: false,
            borderColor: '#3b82f6',
            tension: 0.4
        }
    ]
});

const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    }
});

const getStatusClass = (status) => {
    const statusLower = status?.toLowerCase();
    const classes = {
        'confirmed': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'cancelled': 'bg-red-100 text-red-800',
        'completed': 'bg-blue-100 text-blue-800',
        'paid': 'bg-green-100 text-green-800'
    };
    return classes[statusLower] || 'bg-gray-100 text-gray-800';
};

const fetchDashboardData = async () => {
    loading.value = true;
    try {
        // Get initial dashboard data (includes user, organization, restaurant, dashboard)
        const response = await axios.get('/auth/initial-dashboard');
        
        console.log('Initial dashboard response:', response.data);
        
        if (response.data.success && response.data.data) {
            const data = response.data.data;
            
            // Set dashboard data
            if (data.dashboard) {
                dashboardData.value = data.dashboard;
            }
            
            console.log('Dashboard data:', dashboardData.value);
        } else {
            throw new Error('Invalid response structure');
        }
    } catch (error) {
        console.error('Dashboard data fetch error:', error);
        console.error('Error response:', error.response);
        console.error('Error message:', error.message);
        
        const errorMessage = error.response?.data?.message || error.message || t('login.errors.genericError');
        
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: errorMessage,
            life: 5000
        });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchDashboardData();
});
</script>

<style scoped>
.dashboard-container {
    padding: 1rem;
    background-color: #f3f4f6;
    min-height: 100vh;
    position: relative;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.header {
    border-radius: 8px;
}

.language-dropdown-header {
    width: 150px;
}
</style>

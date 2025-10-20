<template>
    <div class="dashboard-container">
        <Toast />
        
        <!-- Header -->
        <div class="header bg-white shadow-sm p-4 mb-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Partner Dashboard</h1>
                <div class="flex gap-2">
                    <Button label="Notifications" icon="pi pi-bell" severity="secondary" text />
                    <Button label="Profile" icon="pi pi-user" severity="secondary" text />
                    <Button label="Logout" icon="pi pi-sign-out" severity="danger" text @click="handleLogout" />
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card>
                <template #content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Total Bookings</p>
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
                            <p class="text-gray-600 text-sm mb-1">Active Tables</p>
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
                            <p class="text-gray-600 text-sm mb-1">Total Revenue</p>
                            <p class="text-3xl font-bold text-purple-600">{{ stats.revenue }}₾</p>
                        </div>
                        <i class="pi pi-money-bill text-4xl text-purple-400"></i>
                    </div>
                </template>
            </Card>

            <Card>
                <template #content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm mb-1">Pending Orders</p>
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
                        <span>Recent Bookings</span>
                        <Button label="View All" size="small" text />
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
                        <Column field="id" header="ID" sortable></Column>
                        <Column field="customer" header="Customer" sortable></Column>
                        <Column field="table" header="Table" sortable></Column>
                        <Column field="date" header="Date" sortable></Column>
                        <Column field="time" header="Time" sortable></Column>
                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <span 
                                    :class="getStatusClass(slotProps.data.status)"
                                    class="px-2 py-1 rounded text-xs font-semibold"
                                >
                                    {{ slotProps.data.status }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Actions">
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
                    <template #title>Quick Actions</template>
                    <template #content>
                        <div class="flex flex-col gap-2">
                            <Button label="New Booking" icon="pi pi-plus" severity="success" class="w-full" />
                            <Button label="Manage Tables" icon="pi pi-table" severity="info" class="w-full" />
                            <Button label="View Menu" icon="pi pi-list" severity="secondary" class="w-full" />
                            <Button label="Settings" icon="pi pi-cog" severity="secondary" class="w-full" />
                        </div>
                    </template>
                </Card>

                <!-- Chart -->
                <Card>
                    <template #title>Weekly Overview</template>
                    <template #content>
                        <Chart type="line" :data="chartData" :options="chartOptions" class="h-64" />
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

const toast = useToast();

const stats = ref({
    totalBookings: 156,
    activeTables: 12,
    revenue: '45,230',
    pendingOrders: 8
});

const recentBookings = ref([
    { id: 1001, customer: 'გიორგი ბერიძე', table: 'Table 5', date: '2025-10-21', time: '19:00', status: 'Confirmed' },
    { id: 1002, customer: 'ნინო მელაძე', table: 'Table 3', date: '2025-10-21', time: '20:30', status: 'Pending' },
    { id: 1003, customer: 'დავით კაცია', table: 'Table 7', date: '2025-10-22', time: '18:00', status: 'Confirmed' },
    { id: 1004, customer: 'ანა გელაშვილი', table: 'Table 2', date: '2025-10-22', time: '19:30', status: 'Cancelled' },
    { id: 1005, customer: 'ლევან წერეთელი', table: 'Table 1', date: '2025-10-23', time: '21:00', status: 'Confirmed' },
]);

const chartData = ref({
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    datasets: [
        {
            label: 'Bookings',
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
    const classes = {
        'Confirmed': 'bg-green-100 text-green-800',
        'Pending': 'bg-yellow-100 text-yellow-800',
        'Cancelled': 'bg-red-100 text-red-800',
        'Completed': 'bg-blue-100 text-blue-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const handleLogout = async () => {
    try {
        await axios.post('/auth/logout');
        
        toast.add({
            severity: 'success',
            summary: 'Logged Out',
            detail: 'You have been logged out successfully',
            life: 2000
        });

        setTimeout(() => {
            window.location.href = '/login';
        }, 1000);
    } catch (error) {
        console.error('Logout error:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to logout. Please try again.',
            life: 3000
        });
    }
};

onMounted(() => {
    toast.add({
        severity: 'success',
        summary: 'Welcome!',
        detail: 'Dashboard loaded successfully',
        life: 3000
    });
});
</script>

<style scoped>
.dashboard-container {
    padding: 1rem;
    background-color: #f3f4f6;
    min-height: 100vh;
}

.header {
    border-radius: 8px;
}
</style>

<template>
    <div class="bookings-page">
        <Toast />
        
        <!-- Loading Overlay -->
        <div v-if="loading" class="loading-overlay">
            <ProgressSpinner />
        </div>

        <div class="page-wrapper">
            <!-- Hero / Summary -->
            <div class="hero-card">
                <div class="hero-texts">
                    <span class="hero-kicker">FOODLY</span>
                    <h2 class="hero-title">{{ $t('bookings.title') }}</h2>
                    <p class="hero-subtitle">{{ $t('bookings.subtitle') }}</p>
                </div>
                <div class="hero-actions">
                    <div class="view-toggle">
                        <Button 
                            :label="$t('bookings.views.list')" 
                            icon="pi pi-list" 
                            :severity="currentView === 'list' ? 'primary' : 'secondary'"
                            @click="currentView = 'list'"
                        />
                        <Button 
                            :label="$t('bookings.calendar.title')" 
                            icon="pi pi-calendar" 
                            :severity="currentView === 'calendar' ? 'primary' : 'secondary'"
                            @click="currentView = 'calendar'"
                        />
                    </div>
                    <Button 
                        :label="$t('bookings.actions.newBooking')" 
                        icon="pi pi-plus" 
                        class="cta-button"
                        @click="showCreateDialog = true"
                    />
                </div>
            </div>

            <!-- Status Overview -->
            <div class="status-overview">
                <div class="status-pill" v-for="pill in statusPills" :key="pill.key">
                    <span class="status-dot" :style="{ backgroundColor: pill.color }"></span>
                    <div class="status-info">
                        <span class="status-label">{{ pill.label }}</span>
                        <span class="status-count">{{ pill.count }}</span>
                    </div>
                </div>
            </div>
            <div class="content-area">
                <!-- Filters -->
                <Card class="filters-card">
                    <template #content>
                        <div class="filters-grid">
                            <div class="filter-item">
                                <label>{{ $t('bookings.filters.search') }}</label>
                                <div class="input-wrapper">
                                    <i class="pi pi-search"></i>
                                    <InputText 
                                        v-model="filters.search" 
                                        :placeholder="$t('bookings.filters.searchPlaceholder')"
                                        @input="applyFilters"
                                        class="enhanced-input"
                                    />
                                </div>
                            </div>
                            
                            <div class="filter-item">
                                <label>{{ $t('bookings.filters.status') }}</label>
                                <Dropdown 
                                    v-model="filters.status" 
                                    :options="statusOptions" 
                                    optionLabel="label"
                                    optionValue="value"
                                    :placeholder="$t('bookings.filters.allStatuses')"
                                    @change="applyFilters"
                                    showClear
                                    class="enhanced-dropdown"
                                />
                            </div>
                            
                            <div class="filter-item">
                                <label>{{ $t('bookings.filters.date') }}</label>
                                <Calendar 
                                    v-model="filters.date" 
                                    dateFormat="yy-mm-dd"
                                    :placeholder="$t('bookings.filters.selectDate')"
                                    @date-select="applyFilters"
                                    showButtonBar
                                    class="enhanced-calendar"
                                />
                            </div>

                        </div>
                    </template>
                </Card>

                <!-- List View -->
                <Card v-if="currentView === 'list'" class="table-card">
                    <template #header>
                        <div class="table-card-header">
                            <div class="header-content">
                                <div class="header-title-section">
                                    <div class="title-icon">
                                        <i class="pi pi-list"></i>
                                    </div>
                                    <div class="title-text">
                                        <h3>{{ $t('bookings.views.list') }}</h3>
                                        <p class="subtitle">{{ $t('bookings.summary.showing', { count: filteredBookings.length }) }}</p>
                                    </div>
                                </div>
                                <div class="header-stats">
                                    <div class="stat-item">
                                        <div class="stat-icon">
                                            <i class="pi pi-calendar"></i>
                                        </div>
                                        <div class="stat-content">
                                            <span class="stat-label">{{ $t('bookings.summary.total') }}</span>
                                            <span class="stat-value">{{ bookings.length }}</span>
                                        </div>
                                    </div>
                                    <div class="stat-item" v-if="filteredBookings.length !== bookings.length">
                                        <div class="stat-icon filtered">
                                            <i class="pi pi-filter"></i>
                                        </div>
                                        <div class="stat-content">
                                            <span class="stat-label">{{ $t('bookings.summary.filtered') }}</span>
                                            <span class="stat-value">{{ filteredBookings.length }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <DataTable 
                            :value="filteredBookings" 
                            :paginator="true" 
                            :rows="10"
                            :rowsPerPageOptions="[10, 25, 50]"
                            :loading="loading"
                            stripedRows
                            showGridlines
                            class="elevated-table"
                        >
                            <Column field="id" :header="$t('bookings.table.id')" sortable style="width: 80px">
                                <template #body="slotProps">
                                    <div class="booking-id">
                                        <i class="pi pi-hashtag"></i>
                                        <span>{{ slotProps.data.id }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column field="customer_name" :header="$t('bookings.table.customer')" sortable>
                                <template #body="slotProps">
                                    <div class="customer-info">
                                        <div class="customer-avatar">
                                            <i class="pi pi-user"></i>
                                        </div>
                                        <div class="customer-details">
                                            <div class="customer-name">{{ slotProps.data.customer_name }}</div>
                                            <div class="customer-phone">{{ slotProps.data.phone }}</div>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column field="table_name" :header="$t('bookings.table.table')" sortable style="width: 120px">
                                <template #body="slotProps">
                                    <div class="table-info">
                                        <i class="pi pi-table"></i>
                                        <span>{{ slotProps.data.table_name || '-' }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column field="date" :header="$t('bookings.table.date')" sortable style="width: 140px">
                                <template #body="slotProps">
                                    <div class="date-time-info">
                                        <div class="date-display">
                                            <i class="pi pi-calendar"></i>
                                            <span>{{ formatDate(slotProps.data.date) }}</span>
                                        </div>
                                        <div class="time-display">
                                            <i class="pi pi-clock"></i>
                                            <span>{{ slotProps.data.time }}</span>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column field="guests" :header="$t('bookings.table.guests')" sortable style="width: 100px">
                                <template #body="slotProps">
                                    <div class="guests-info">
                                        <i class="pi pi-users"></i>
                                        <span>{{ slotProps.data.guests }}</span>
                                    </div>
                                </template>
                            </Column>
                            <Column field="status" :header="$t('bookings.table.status')" sortable style="width: 150px">
                                <template #body="slotProps">
                                    <div class="status-container">
                                        <Tag 
                                            :value="translateStatus(slotProps.data.status)" 
                                            :severity="getStatusSeverity(slotProps.data.status)"
                                            :icon="getStatusIcon(slotProps.data.status)"
                                            :data-status="slotProps.data.status"
                                            class="status-tag"
                                        />
                                    </div>
                                </template>
                            </Column>
                            <Column :header="$t('common.actions')" style="width: 250px">
                                <template #body="slotProps">
                                    <div class="action-buttons">
                                        <Button 
                                            icon="pi pi-eye" 
                                            severity="info" 
                                            text 
                                            rounded
                                            v-tooltip.top="$t('common.view')"
                                            @click="viewBooking(slotProps.data)"
                                        />
                                        <Button 
                                            icon="pi pi-check" 
                                            severity="success" 
                                            text 
                                            rounded
                                            v-tooltip.top="$t('bookings.actions.confirm')"
                                            @click="confirmBooking(slotProps.data)"
                                            v-if="slotProps.data.status === 'pending'"
                                        />
                                        <Button 
                                            icon="pi pi-dollar" 
                                            severity="success" 
                                            text 
                                            rounded
                                            v-tooltip.top="$t('bookings.actions.markAsPaid')"
                                            @click="markAsPaid(slotProps.data)"
                                            v-if="slotProps.data.status === 'confirmed'"
                                        />
                                        <Button 
                                            icon="pi pi-check-circle" 
                                            severity="success" 
                                            text 
                                            rounded
                                            v-tooltip.top="$t('bookings.actions.markAsCompleted')"
                                            @click="markAsCompleted(slotProps.data)"
                                            v-if="slotProps.data.status === 'confirmed'"
                                        />
                                        <Button 
                                            icon="pi pi-user-minus" 
                                            severity="warning" 
                                            text 
                                            rounded
                                            v-tooltip.top="$t('bookings.actions.markAsNoShow')"
                                            @click="markAsNoShow(slotProps.data)"
                                            v-if="slotProps.data.status === 'confirmed'"
                                        />
                                        <Button 
                                            icon="pi pi-times" 
                                            severity="danger" 
                                            text 
                                            rounded
                                            v-tooltip.top="$t('bookings.actions.cancel')"
                                            @click="cancelBooking(slotProps.data)"
                                            v-if="['pending', 'confirmed'].includes(slotProps.data.status)"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>

                <!-- Calendar View -->
                <Card v-else class="calendar-card">
                    <template #header>
                        <div class="table-card-header">
                            <div>
                                <h3>{{ $t('bookings.calendar.title') }}</h3>
                                <p>{{ $t('bookings.calendar.loading') }}</p>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <ReservationCalendar 
                            :organizationId="organizationId"
                            :restaurantId="restaurantId"
                            @view-reservation="viewBookingById"
                        />
                    </template>
                </Card>
            </div>
        </div>

        <!-- View Booking Dialog -->
        <Dialog 
            v-model:visible="showViewDialog" 
            :header="$t('bookings.viewDialog.title')"
            :style="{ width: '600px' }"
            modal
        >
            <div v-if="selectedBooking" class="booking-details">
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.id') }}:</span>
                    <span class="detail-value">{{ selectedBooking.id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.customer') }}:</span>
                    <span class="detail-value">{{ selectedBooking.customer_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.phone') }}:</span>
                    <span class="detail-value">{{ selectedBooking.phone }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.date') }}:</span>
                    <span class="detail-value">{{ formatDate(selectedBooking.date) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.time') }}:</span>
                    <span class="detail-value">{{ selectedBooking.time }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.guests') }}:</span>
                    <span class="detail-value">{{ selectedBooking.guests }}</span>
                </div>
                <!-- <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.table') }}:</span>
                    <span class="detail-value">{{ selectedBooking.table_name }}</span>
                </div> -->
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.status') }}:</span>
                    <Tag 
                        :value="translateStatus(selectedBooking.status)" 
                        :severity="getStatusSeverity(selectedBooking.status)"
                        :data-status="selectedBooking.status"
                        class="status-tag"
                    />
                </div>
            </div>
        </Dialog>

        <!-- Create Booking Dialog -->
        <Dialog 
            v-model:visible="showCreateDialog" 
            :header="$t('bookings.actions.newBooking')"
            :style="{ width: '600px' }"
            modal
        >
            <p class="text-gray-600 mb-4">{{ $t('bookings.createDialog.message') }}</p>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import Calendar from 'primevue/calendar';
import Tag from 'primevue/tag';
import Tooltip from 'primevue/tooltip';
import ReservationCalendar from './ReservationCalendar.vue';

const toast = useToast();
const { t } = useI18n();

const loading = ref(true);
const bookings = ref([]);
const selectedBooking = ref(null);
const showViewDialog = ref(false);
const showCreateDialog = ref(false);
const currentView = ref('list'); // 'list' or 'calendar'

// Get org and restaurant IDs from localStorage or fetch from API
const organizationId = ref(null);
const restaurantId = ref(null);

// Initialize IDs from localStorage
const initializeIds = () => {
    const storedOrgId = localStorage.getItem('organizationId');
    const storedRestId = localStorage.getItem('restaurantId');
    
    if (storedOrgId) {
        organizationId.value = parseInt(storedOrgId, 10);
    }
    if (storedRestId) {
        restaurantId.value = parseInt(storedRestId, 10);
    }
};

const filters = ref({
    search: '',
    status: null,
    date: null
});

const statusOptions = computed(() => [
    { label: t('bookings.status.pending'), value: 'pending' },
    { label: t('bookings.status.confirmed'), value: 'confirmed' },
    { label: t('bookings.status.completed'), value: 'completed' },
    { label: t('bookings.status.cancelled'), value: 'cancelled' }
    // Note: 'paid' status is temporarily hidden
]);


const statusCounts = computed(() => {
    const counts = {
        pending: 0,
        confirmed: 0,
        completed: 0,
        cancelled: 0,
        no_show: 0
    };

    // Filter out paid status bookings from counts
    const visibleBookings = bookings.value.filter(b => b.status !== 'paid');

    visibleBookings.forEach((booking) => {
        const key = (booking.status || '').toLowerCase().replace('-', '_');
        if (counts[key] !== undefined) {
            counts[key] += 1;
        }
    });

    return counts;
});

const statusPills = computed(() => [
    {
        key: 'pending',
        label: t('bookings.status.pending'),
        count: statusCounts.value.pending,
        color: '#facc15'
    },
    {
        key: 'confirmed',
        label: t('bookings.status.confirmed'),
        count: statusCounts.value.confirmed,
        color: '#60a5fa'
    },
    {
        key: 'completed',
        label: t('bookings.status.completed'),
        count: statusCounts.value.completed,
        color: '#34d399'
    },
    {
        key: 'cancelled',
        label: t('bookings.status.cancelled'),
        count: statusCounts.value.cancelled,
        color: '#f87171'
    },
    {
        key: 'no_show',
        label: t('bookings.status.no_show'),
        count: statusCounts.value.no_show,
        color: '#fb923c'
    }
]);

const filteredBookings = computed(() => {
    let result = bookings.value;

    // Hide paid status bookings temporarily
    result = result.filter(b => b.status !== 'paid');

    // Search filter
    if (filters.value.search) {
        const searchLower = filters.value.search.toLowerCase();
        result = result.filter(b => 
            b.customer_name?.toLowerCase().includes(searchLower) ||
            b.phone?.includes(searchLower) ||
            b.table_name?.toLowerCase().includes(searchLower)
        );
    }

    // Status filter
    if (filters.value.status) {
        result = result.filter(b => b.status === filters.value.status);
    }

    // Date filter
    if (filters.value.date) {
        const filterDate = formatDateForFilter(filters.value.date);
        result = result.filter(b => b.date === filterDate);
    }

    return result;
});

const translateStatus = (status) => {
    const statusMap = {
        'pending': t('bookings.status.pending'),
        'confirmed': t('bookings.status.confirmed'),
        'completed': t('bookings.status.completed'),
        'cancelled': t('bookings.status.cancelled')
    };
    return statusMap[status?.toLowerCase()] || status;
};

const getStatusSeverity = (status) => {
    const severityMap = {
        'pending': 'warning',
        'confirmed': 'success',
        'completed': 'info',
        'cancelled': 'danger',
        'no-show': 'danger'
    };
    return severityMap[status?.toLowerCase()] || 'secondary';
};

const getStatusIcon = (status) => {
    const iconMap = {
        'pending': 'pi pi-clock',
        'confirmed': 'pi pi-check-circle',
        'completed': 'pi pi-check',
        'cancelled': 'pi pi-times',
        'no-show': 'pi pi-user-minus'
    };
    return iconMap[status?.toLowerCase()] || 'pi pi-circle';
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('ka-GE', { 
        year: 'numeric', 
        month: '2-digit', 
        day: '2-digit' 
    });
};

const formatDateForFilter = (date) => {
    if (!date) return null;
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const applyFilters = () => {
    // Filters are reactive, so this just triggers a re-compute
};

const fetchBookings = async () => {
    loading.value = true;
    try {
        // Fetch reservations directly from simplified endpoint
        const response = await axios.get('/reservations', {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (response.data.success && response.data.data) {
            bookings.value = response.data.data.map(reservation => ({
                id: reservation.id,
                customer_name: reservation.name,
                phone: reservation.phone,
                table_name: reservation.table_name || reservation.table_number || '-',
                date: reservation.reservation_date,
                time: `${reservation.time_from} - ${reservation.time_to}`,
                guests: reservation.guests_count,
                status: reservation.status,
                email: reservation.email,
                occasion: reservation.occasion,
                notes: reservation.notes,
                type: reservation.type,
                created_at: reservation.created_at
            }));
        }
    } catch (error) {
        console.error('Failed to fetch bookings:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('bookings.errors.fetchFailed'),
            life: 5000
        });
    } finally {
        loading.value = false;
    }
};

const viewBooking = (booking) => {
    selectedBooking.value = booking;
    showViewDialog.value = true;
};

const viewBookingById = async (reservationId) => {
    // Find booking in current list or fetch it
    const booking = bookings.value.find(b => b.id === reservationId);
    if (booking) {
        viewBooking(booking);
    } else {
        // If not in current list, fetch the bookings again
        await fetchBookings();
        const foundBooking = bookings.value.find(b => b.id === reservationId);
        if (foundBooking) {
            viewBooking(foundBooking);
        }
    }
};

const confirmBooking = async (booking) => {
    try {
        // Get organization and restaurant IDs
        const dashboardResponse = await axios.get('/auth/initial-dashboard');
        const data = dashboardResponse.data.data;
        const orgId = data.organization?.id;
        const restId = data.restaurant?.id;

        await axios.post(`/organizations/${orgId}/restaurants/${restId}/reservations/${booking.id}/confirm`);
        
        toast.add({
            severity: 'success',
            summary: t('common.success'),
            detail: t('bookings.messages.confirmed'),
            life: 3000
        });
        
        // Update local status
        booking.status = 'confirmed';
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('bookings.errors.confirmFailed'),
            life: 5000
        });
    }
};

const cancelBooking = async (booking) => {
    try {
        // Get organization and restaurant IDs
        const dashboardResponse = await axios.get('/auth/initial-dashboard');
        const data = dashboardResponse.data.data;
        const orgId = data.organization?.id;
        const restId = data.restaurant?.id;

        await axios.post(`/organizations/${orgId}/restaurants/${restId}/reservations/${booking.id}/cancel`, {
            reason: 'Cancelled by staff'
        });
        
        toast.add({
            severity: 'success',
            summary: t('common.success'),
            detail: t('bookings.messages.cancelled'),
            life: 3000
        });
        
        // Update local status
        booking.status = 'cancelled';
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('bookings.errors.cancelFailed'),
            life: 5000
        });
    }
};

const markAsPaid = async (booking) => {
    try {
        // Get organization and restaurant IDs
        const dashboardResponse = await axios.get('/auth/initial-dashboard');
        const data = dashboardResponse.data.data;
        const orgId = data.organization?.id;
        const restId = data.restaurant?.id;

        await axios.post(`/organizations/${orgId}/restaurants/${restId}/reservations/${booking.id}/paid`);
        
        toast.add({
            severity: 'success',
            summary: t('common.success'),
            detail: t('bookings.messages.markedAsPaid'),
            life: 3000
        });
        
        // Refresh bookings
        await fetchBookings();
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('bookings.errors.paidFailed'),
            life: 5000
        });
    }
};

const markAsCompleted = async (booking) => {
    try {
        // Get organization and restaurant IDs
        const dashboardResponse = await axios.get('/auth/initial-dashboard');
        const data = dashboardResponse.data.data;
        const orgId = data.organization?.id;
        const restId = data.restaurant?.id;

        await axios.post(`/organizations/${orgId}/restaurants/${restId}/reservations/${booking.id}/complete`);
        
        toast.add({
            severity: 'success',
            summary: t('common.success'),
            detail: t('bookings.messages.markedAsCompleted'),
            life: 3000
        });
        
        // Update local status
        booking.status = 'completed';
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('bookings.errors.completeFailed'),
            life: 5000
        });
    }
};

const markAsNoShow = async (booking) => {
    try {
        // Get organization and restaurant IDs
        const dashboardResponse = await axios.get('/auth/initial-dashboard');
        const data = dashboardResponse.data.data;
        const orgId = data.organization?.id;
        const restId = data.restaurant?.id;

        await axios.post(`/organizations/${orgId}/restaurants/${restId}/reservations/${booking.id}/no-show`);
        
        toast.add({
            severity: 'success',
            summary: t('common.success'),
            detail: t('bookings.messages.markedAsNoShow'),
            life: 3000
        });
        
        // Update local status
        booking.status = 'no-show';
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('bookings.errors.noShowFailed'),
            life: 5000
        });
    }
};

onMounted(() => {
    initializeIds();
    fetchBookings();
});
</script>

<style scoped>
.bookings-page {
    min-height: 100vh;
    background: linear-gradient(180deg, #f0f4ff 0%, #ffffff 45%);
    padding: 2rem 2.5rem;
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

.page-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.hero-card {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 40%, #8b5cf6 100%);
    border-radius: 24px;
    padding: 2.25rem 2.5rem;
    color: #ffffff;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 1.5rem;
    box-shadow: 0 25px 50px -12px rgba(79, 70, 229, 0.4);
}

.hero-texts {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.hero-kicker {
    font-size: 0.85rem;
    letter-spacing: 0.25rem;
    text-transform: uppercase;
    opacity: 0.8;
}

.hero-title {
    font-size: 2.25rem;
    font-weight: 700;
    margin: 0;
}

.hero-subtitle {
    margin: 0;
    font-size: 1rem;
    max-width: 420px;
    color: rgba(255, 255, 255, 0.85);
}

.hero-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.view-toggle {
    display: flex;
    gap: 0.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.25rem;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(12px);
}

.view-toggle .p-button {
    border-radius: 4px;
}

.cta-button {
    background: #22c55e;
    border-color: #22c55e;
}

.cta-button:hover {
    background: #16a34a;
    border-color: #16a34a;
}

.status-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
}

.status-pill {
    background: #ffffff;
    border-radius: 16px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(148, 163, 184, 0.25);
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.status-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.status-label {
    font-size: 0.85rem;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.status-count {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
}

.content-area {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 0.5rem 0;
}

.page-subtitle {
    color: #718096;
    margin: 0;
}

.filters-card {
    margin: 0;
    border: none;
    background: transparent;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper i {
    position: absolute;
    left: 0.75rem;
    color: #94a3b8;
    font-size: 0.9rem;
}

.input-wrapper .p-inputtext {
    width: 100%;
    padding-left: 2.25rem;
    border-radius: 10px;
}

.filter-item label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #4a5568;
    font-size: 0.875rem;
}

/* Enhanced Filter Components */
.enhanced-input {
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    transition: all 0.2s ease;
    font-size: 0.9rem;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
}

.enhanced-input:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    outline: none;
}

.enhanced-dropdown {
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    transition: all 0.2s ease;
}

.enhanced-dropdown:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.enhanced-calendar {
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    transition: all 0.2s ease;
}

.enhanced-calendar:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Enhanced Input Wrapper */
.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper i {
    position: absolute;
    left: 0.75rem;
    color: #94a3b8;
    font-size: 0.9rem;
    z-index: 1;
}

.table-card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px 16px 0 0;
    padding: 1.5rem 2rem;
    border-bottom: 2px solid #e2e8f0;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.header-title-section {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.title-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.title-text h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.title-text .subtitle {
    margin: 0.25rem 0 0;
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 500;
}

.header-stats {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: white;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: all 0.2s ease;
}

.stat-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
}

.stat-icon.filtered {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.stat-content {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.stat-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1e293b;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .header-stats {
        width: 100%;
        justify-content: space-between;
    }
    
    .stat-item {
        flex: 1;
        justify-content: center;
    }
}

.elevated-table {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 18px 35px rgba(15, 23, 42, 0.08);
}

.calendar-card {
    border-radius: 20px;
    overflow: hidden;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
}

/* Enhanced Table Styling */
.booking-id {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #6366f1;
}

.booking-id i {
    font-size: 0.75rem;
    opacity: 0.7;
}

.customer-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.customer-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.customer-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.customer-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.9rem;
}

.customer-phone {
    color: #64748b;
    font-size: 0.8rem;
}

.table-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #475569;
}

.table-info i {
    color: #6366f1;
    font-size: 0.9rem;
}

.date-time-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.date-display, .time-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
}

.date-display {
    color: #1e293b;
    font-weight: 500;
}

.time-display {
    color: #64748b;
}

.date-display i, .time-display i {
    font-size: 0.75rem;
    opacity: 0.7;
}

.guests-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #1e293b;
    justify-content: center;
    background: #f1f5f9;
    padding: 0.5rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.guests-info i {
    color: #6366f1;
    font-size: 0.9rem;
}

.status-container {
    display: flex;
    justify-content: center;
}

.status-tag {
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Custom Status Colors */
:deep(.p-tag.p-tag-success) {
    background: linear-gradient(135deg, #22c55e, #16a34a) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(34, 197, 94, 0.3) !important;
}

:deep(.p-tag.p-tag-warning) {
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3) !important;
}

:deep(.p-tag.p-tag-info) {
    background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3) !important;
}

:deep(.p-tag.p-tag-danger) {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: white !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3) !important;
}

/* Status-specific styling for better distinction */
:deep(.p-tag[data-status="pending"]) {
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    color: white !important;
}

:deep(.p-tag[data-status="confirmed"]) {
    background: linear-gradient(135deg, #22c55e, #16a34a) !important;
    color: white !important;
}

:deep(.p-tag[data-status="completed"]) {
    background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
    color: white !important;
}

:deep(.p-tag[data-status="cancelled"]) {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: white !important;
}

/* Enhanced DataTable Styling */
:deep(.p-datatable) {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
}

:deep(.p-datatable .p-datatable-header) {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 2px solid #e2e8f0;
    padding: 1.5rem;
}

:deep(.p-datatable .p-datatable-thead > tr > th) {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.8rem;
    padding: 1rem 0.75rem;
    border: none;
}

:deep(.p-datatable .p-datatable-tbody > tr) {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f1f5f9;
}

:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
}

:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 1.25rem 0.75rem;
    border: none;
    vertical-align: middle;
}

:deep(.p-datatable .p-datatable-tbody > tr:nth-child(even)) {
    background: rgba(248, 250, 252, 0.5);
}

/* Enhanced Action Buttons */
.action-buttons .p-button {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-buttons .p-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.action-buttons .p-button.p-button-success {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
}

.action-buttons .p-button.p-button-info {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border: none;
}

.action-buttons .p-button.p-button-warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border: none;
}

.action-buttons .p-button.p-button-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border: none;
}

.booking-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    background: #f7fafc;
    border-radius: 0.5rem;
}

.detail-label {
    font-weight: 600;
    color: #4a5568;
}

.detail-value {
    color: #2d3748;
}

@media (max-width: 768px) {
    .bookings-page {
        padding: 1.5rem 1rem;
    }

    .hero-card {
        padding: 1.75rem 1.5rem;
    }

    .hero-title {
        font-size: 1.75rem;
    }

    .status-overview {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .elevated-table {
        box-shadow: none;
    }
}
</style>

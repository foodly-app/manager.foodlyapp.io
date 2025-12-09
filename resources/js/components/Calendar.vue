<template>
    <div class="calendar-page">
        <Toast />
        
        <!-- Loading Overlay -->
        <div v-if="loading" class="loading-overlay">
            <ProgressSpinner />
        </div>

        <!-- Header -->
        <div class="page-header">
            <div class="header-left">
                <h2 class="page-title">{{ $t('bookings.calendar.title') }}</h2>
                <p class="page-subtitle">{{ $t('bookings.subtitle') }}</p>
            </div>
        </div>

        <!-- Status Legend -->
        <div v-if="organizationId && restaurantId" class="status-legend">
            <div v-for="legend in statusLegend" :key="legend.key" class="legend-item">
                <span class="legend-dot" :style="{ backgroundColor: legend.color }"></span>
                <span class="legend-label">{{ legend.label }}</span>
            </div>
        </div>

        <!-- Debug Info -->
        <div v-if="false" style="padding: 2rem; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; margin-bottom: 1rem;">
            <h3 style="color: #856404; margin: 0 0 1rem 0;">⚠️ Missing IDs</h3>
            <p style="margin: 0.5rem 0;">Organization ID: {{ organizationId || 'NOT SET' }}</p>
            <p style="margin: 0.5rem 0;">Restaurant ID: {{ restaurantId || 'NOT SET' }}</p>
            <p style="margin: 1rem 0 0 0; color: #856404;">Calendar cannot load without these IDs. Check localStorage or API.</p>
        </div>

        <!-- Calendar View -->
        <ReservationCalendar 
            :organizationId="organizationId || 0"
            :restaurantId="restaurantId || 0"
            @view-reservation="viewReservation"
        />

        <!-- View Reservation Dialog -->
        <Dialog 
            v-model:visible="showViewDialog" 
            :header="$t('bookings.viewDialog.title')"
            :style="{ width: '600px' }"
            modal
        >
            <div v-if="selectedReservation" class="booking-details">
                <div v-if="selectedReservation.reservation_number" class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.reservationNumber') }}:</span>
                    <span class="detail-value">{{ selectedReservation.reservation_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.id') }}:</span>
                    <span class="detail-value">{{ selectedReservation.id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.customer') }}:</span>
                    <span class="detail-value">{{ selectedReservation.customer_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.phone') }}:</span>
                    <span class="detail-value">{{ selectedReservation.phone || '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.email') }}:</span>
                    <span class="detail-value">{{ selectedReservation.email || '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.date') }}:</span>
                    <span class="detail-value">{{ formatDate(selectedReservation.date) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.time') }}:</span>
                    <span class="detail-value">{{ selectedReservation.time_range || selectedReservation.time || '-' }}</span>
                </div>
                <div v-if="selectedReservation.duration" class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.duration') }}:</span>
                    <span class="detail-value">{{ formatDuration(selectedReservation.duration) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.guests') }}:</span>
                    <span class="detail-value">{{ selectedReservation.party_size }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.table') }}:</span>
                    <span class="detail-value">{{ selectedReservation.table_name || '-' }}</span>
                </div>
                <div v-if="selectedReservation.place_name" class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.place') }}:</span>
                    <span class="detail-value">{{ selectedReservation.place_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.status') }}:</span>
                    <Tag 
                        :value="translateStatus(selectedReservation.status)" 
                        :severity="getStatusSeverity(selectedReservation.status)"
                    />
                </div>
                <div v-if="selectedReservation.payment_status" class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.paymentStatus') }}:</span>
                    <Tag 
                        :value="formatPaymentStatus(selectedReservation.payment_status)" 
                        :severity="getPaymentSeverity(selectedReservation.payment_status)"
                    />
                </div>
                <div v-if="selectedReservation.deposit_amount !== null" class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.depositAmount') }}:</span>
                    <span class="detail-value">{{ formatCurrency(selectedReservation.deposit_amount) }}</span>
                </div>
                <div v-if="selectedReservation.special_requests" class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.notes') }}:</span>
                    <span class="detail-value">{{ selectedReservation.special_requests }}</span>
                </div>
            </div>
            
            <template #footer>
                <div class="dialog-footer">
                    <Button 
                        :label="$t('common.close')" 
                        severity="secondary" 
                        @click="showViewDialog = false"
                    />
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import ProgressSpinner from 'primevue/progressspinner';
import Toast from 'primevue/toast';
import ReservationCalendar from './ReservationCalendar.vue';

const toast = useToast();
const { t, locale } = useI18n();

const loading = ref(true);
const selectedReservation = ref(null);
const showViewDialog = ref(false);
const organizationId = ref(null);
const restaurantId = ref(null);

const STATUS_ORDER = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];
const STATUS_COLORS = {
    pending: '#facc15',
    confirmed: '#3b82f6',
    seated: '#8b5cf6',
    completed: '#10b981',
    cancelled: '#ef4444',
    no_show: '#f97316'
};

// Initialize IDs from localStorage or fetch from API
const initializeIds = async () => {
    const storedOrgId = localStorage.getItem('organizationId');
    const storedRestId = localStorage.getItem('restaurantId');
    
    console.log('Calendar - localStorage IDs:', { storedOrgId, storedRestId });
    
    if (storedOrgId && storedRestId) {
        organizationId.value = parseInt(storedOrgId, 10);
        restaurantId.value = parseInt(storedRestId, 10);
        console.log('Calendar - Using stored IDs:', { orgId: organizationId.value, restId: restaurantId.value });
        loading.value = false;
        return;
    }
    
    // If not in localStorage, fetch from API
    try {
    console.log('Calendar - Fetching IDs from API...');
    const response = await axios.get('/auth/initial-dashboard');
        
        console.log('Calendar - API response:', response.data);
        
        if (response.data.success && response.data.data) {
            const data = response.data.data;
            const orgId = data.organization_id || data.organization?.id;
            const restId = data.restaurant_id || data.restaurant?.id;

            if (orgId) {
                organizationId.value = Number(orgId);
                localStorage.setItem('organizationId', orgId);
            }
            if (restId) {
                restaurantId.value = Number(restId);
                localStorage.setItem('restaurantId', restId);
            }
            console.log('Calendar - IDs set from API:', { orgId: organizationId.value, restId: restaurantId.value });
        }
    } catch (error) {
        console.error('Calendar - Failed to fetch initial data:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: 'Failed to load organization data',
            life: 5000
        });
    } finally {
        loading.value = false;
    }
};

const viewReservation = async (reservationId) => {
    try {
        loading.value = true;
        
        const response = await axios.get(
            `/organizations/${organizationId.value}/restaurants/${restaurantId.value}/reservations/${reservationId}`
        );
        
        const reservation = unwrapObject(response.data);

        if (reservation) {
            const customer = reservation.customer || {};
            const payment = reservation.payment || {};
            const table = reservation.table || {};

            selectedReservation.value = {
                id: reservation.id,
                reservation_number: reservation.reservation_number,
                customer_name: customer.name || reservation.customer_name || '-',
                phone: customer.phone || reservation.customer_phone || null,
                email: customer.email || reservation.customer_email || null,
                date: reservation.date || reservation.reservation_date,
                time: reservation.time,
                time_range: formatTimeRange(reservation.date || reservation.reservation_date, reservation.time, reservation.duration),
                duration: reservation.duration,
                party_size: reservation.party_size || reservation.guests_count,
                status: reservation.status,
                special_requests: reservation.special_requests || reservation.notes,
                table_name: table.table_number || table.name || null,
                place_name: table.place_name || table.place?.name || null,
                payment_status: payment.status || reservation.payment_status || null,
                deposit_amount: payment.deposit_amount ?? reservation.deposit_amount ?? null,
                total_amount: payment.total_amount ?? reservation.total_amount ?? null
            };
            showViewDialog.value = true;
    }
    } catch (error) {
        console.error('Failed to fetch reservation details:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || 'Failed to load reservation details',
            life: 5000
        });
    } finally {
        loading.value = false;
    }
};

const translateStatus = (status) => {
    const normalized = normalizeStatus(status);
    const statusMap = {
        pending: t('bookings.status.pending'),
        confirmed: t('bookings.status.confirmed'),
        seated: t('bookings.status.seated'),
        completed: t('bookings.status.completed'),
        cancelled: t('bookings.status.cancelled'),
        no_show: t('bookings.status.no_show')
    };
    return statusMap[normalized] || status;
};

const getStatusSeverity = (status) => {
    const severityMap = {
        pending: 'warning',
        confirmed: 'success',
        seated: 'info',
        completed: 'info',
        cancelled: 'danger',
        no_show: 'danger'
    };
    return severityMap[normalizeStatus(status)] || 'secondary';
};

const getPaymentSeverity = (paymentStatus) => {
    const normalized = (paymentStatus || '').toLowerCase();
    const severityMap = {
        paid: 'success',
        pending: 'warning',
        partial: 'info',
        refunded: 'info',
        failed: 'danger'
    };
    return severityMap[normalized] || 'secondary';
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

const formatDuration = (duration) => {
    if (!duration) return '-';
    return `${duration}h`;
};

const formatTimeRange = (dateString, timeString, duration) => {
    if (!timeString) return null;
    if (!duration) return timeString;

    const [hours, minutes] = timeString.split(':').map(Number);
    if (Number.isNaN(hours)) {
        return timeString;
    }

    const baseDate = dateString ? new Date(`${dateString}T${timeString}`) : new Date();
    baseDate.setHours(hours, minutes || 0, 0, 0);

    const endDate = new Date(baseDate.getTime() + duration * 60 * 60 * 1000);

    return `${formatTime(baseDate)} - ${formatTime(endDate)}`;
};

const formatTime = (value) => {
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '-';
    }
    return date.toLocaleTimeString(locale.value === 'ka' ? 'ka-GE' : 'en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatPaymentStatus = (status) => {
    if (!status) return '-';
    return status
        .toString()
        .split(/[-_]/)
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join(' ');
};

const formatCurrency = (amount) => {
    if (amount === null || amount === undefined) return '-';
    try {
        return new Intl.NumberFormat(locale.value === 'ka' ? 'ka-GE' : 'en-US', {
            style: 'currency',
            currency: 'GEL',
            minimumFractionDigits: 2
        }).format(Number(amount));
    } catch (error) {
        return Number(amount).toFixed(2);
    }
};

const unwrapPayload = (payload) => {
    if (payload === null || payload === undefined) {
        return payload;
    }

    if (Array.isArray(payload)) {
        return payload;
    }

    if (Object.prototype.hasOwnProperty.call(payload, 'data')) {
        return unwrapPayload(payload.data);
    }

    return payload;
};

const unwrapObject = (payload) => {
    const unwrapped = unwrapPayload(payload);
    return unwrapped && !Array.isArray(unwrapped) ? unwrapped : null;
};

const normalizeStatus = (status) => {
    return (status || '')
        .toString()
        .toLowerCase()
        .replace('-', '_');
};

const statusLegend = computed(() => STATUS_ORDER.map((key) => ({
    key,
    color: STATUS_COLORS[key],
    label: translateStatus(key)
})));

onMounted(async () => {
    await initializeIds();
});
</script>

<style scoped>
.calendar-page {
    padding: 1.5rem;
    max-width: 1400px;
    margin: 0 auto;
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

.page-header {
    margin-bottom: 2rem;
}

.status-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 0.35rem 0.75rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.legend-label {
    font-size: 0.85rem;
    color: #334155;
    font-weight: 500;
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

.booking-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f7fafc;
    border-radius: 6px;
}

.detail-label {
    font-weight: 600;
    color: #4a5568;
}

.detail-value {
    color: #2d3748;
}

.dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

@media (max-width: 768px) {
    .calendar-page {
        padding: 1rem;
    }
    
    .status-legend {
        flex-direction: column;
        align-items: flex-start;
    }

    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

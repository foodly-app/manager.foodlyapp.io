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

        <!-- Calendar View -->
        <ReservationCalendar 
            v-if="organizationId && restaurantId"
            :organizationId="organizationId"
            :restaurantId="restaurantId"
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
                    <span class="detail-value">{{ selectedReservation.time }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.guests') }}:</span>
                    <span class="detail-value">{{ selectedReservation.guests }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.table') }}:</span>
                    <span class="detail-value">{{ selectedReservation.table_name || '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.status') }}:</span>
                    <Tag 
                        :value="translateStatus(selectedReservation.status)" 
                        :severity="getStatusSeverity(selectedReservation.status)"
                    />
                </div>
                <div v-if="selectedReservation.notes" class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.notes') }}:</span>
                    <span class="detail-value">{{ selectedReservation.notes }}</span>
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
import { ref, onMounted } from 'vue';
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
const { t } = useI18n();

const loading = ref(true);
const selectedReservation = ref(null);
const showViewDialog = ref(false);
const organizationId = ref(null);
const restaurantId = ref(null);

// Initialize IDs from localStorage or fetch from API
const initializeIds = async () => {
    const storedOrgId = localStorage.getItem('organizationId');
    const storedRestId = localStorage.getItem('restaurantId');
    
    if (storedOrgId && storedRestId) {
        organizationId.value = parseInt(storedOrgId, 10);
        restaurantId.value = parseInt(storedRestId, 10);
        loading.value = false;
        return;
    }
    
    // If not in localStorage, fetch from API
    try {
        const response = await axios.get('/auth/initial-dashboard');
        
        if (response.data.success && response.data.data) {
            const data = response.data.data;
            
            if (data.organization?.id) {
                organizationId.value = data.organization.id;
                localStorage.setItem('organizationId', data.organization.id);
            }
            if (data.restaurant?.id) {
                restaurantId.value = data.restaurant.id;
                localStorage.setItem('restaurantId', data.restaurant.id);
            }
        }
    } catch (error) {
        console.error('Failed to fetch initial data:', error);
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
        
        if (response.data.success && response.data.data) {
            const reservation = response.data.data;
            selectedReservation.value = {
                id: reservation.id,
                customer_name: reservation.name,
                phone: reservation.phone,
                email: reservation.email,
                date: reservation.reservation_date,
                time: `${reservation.time_from} - ${reservation.time_to}`,
                guests: reservation.guests_count,
                status: reservation.status,
                notes: reservation.notes,
                table_name: reservation.table?.name || '-',
                type: reservation.type,
                occasion: reservation.occasion
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
        'cancelled': 'danger'
    };
    return severityMap[status?.toLowerCase()] || 'secondary';
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
    
    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

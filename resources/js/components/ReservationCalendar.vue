<template>
    <div class="reservation-calendar">
        <FullCalendar :options="calendarOptions" />
        
        <!-- Event Details Dialog -->
        <Dialog 
            v-model:visible="showEventDialog" 
            :header="$t('bookings.calendar.eventDetails')"
            :modal="true"
            :style="{ width: '500px' }"
            :closable="true"
        >
            <div v-if="selectedEvent" class="event-details">
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.customer') }}:</span>
                    <span class="detail-value">{{ selectedEvent.extendedProps.customer_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.phone') }}:</span>
                    <span class="detail-value">{{ selectedEvent.extendedProps.customer_phone }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.email') }}:</span>
                    <span class="detail-value">{{ selectedEvent.extendedProps.customer_email || '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.guests') }}:</span>
                    <span class="detail-value">{{ selectedEvent.extendedProps.guests_count }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.status') }}:</span>
                    <Tag 
                        :value="selectedEvent.extendedProps.status_label" 
                        :severity="getStatusSeverity(selectedEvent.extendedProps.status)"
                    />
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.time') }}:</span>
                    <span class="detail-value">{{ formatTime(selectedEvent.start) }} - {{ formatTime(selectedEvent.end) }}</span>
                </div>
                <div v-if="selectedEvent.extendedProps.table_name" class="detail-row">
                    <span class="detail-label">{{ $t('bookings.table.table') }}:</span>
                    <span class="detail-value">{{ selectedEvent.extendedProps.table_name }}</span>
                </div>
                <div v-if="selectedEvent.extendedProps.place_name" class="detail-row">
                    <span class="detail-label">{{ $t('places.title') }}:</span>
                    <span class="detail-value">{{ selectedEvent.extendedProps.place_name }}</span>
                </div>
                <div v-if="selectedEvent.extendedProps.special_requests" class="detail-row">
                    <span class="detail-label">{{ $t('bookings.specialRequests') }}:</span>
                    <span class="detail-value">{{ selectedEvent.extendedProps.special_requests }}</span>
                </div>
            </div>

            <template #footer>
                <Button 
                    :label="$t('common.close')" 
                    icon="pi pi-times" 
                    class="p-button-text" 
                    @click="showEventDialog = false"
                />
                <Button 
                    :label="$t('common.view')" 
                    icon="pi pi-eye" 
                    @click="viewReservation"
                />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const { t, locale } = useI18n();
const toast = useToast();

const props = defineProps({
    organizationId: {
        type: Number,
        required: true
    },
    restaurantId: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['view-reservation']);

// Data
const events = ref([]);
const loading = ref(false);
const showEventDialog = ref(false);
const selectedEvent = ref(null);

// Calendar Options
const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    buttonText: {
        today: t('bookings.calendar.today'),
        month: t('bookings.calendar.month'),
        week: t('bookings.calendar.week'),
        day: t('bookings.calendar.day')
    },
    locale: locale.value === 'ka' ? 'ka' : 'en',
    events: events.value,
    eventClick: handleEventClick,
    datesSet: handleDatesSet,
    height: 'auto',
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: false
    },
    slotLabelFormat: {
        hour: '2-digit',
        minute: '2-digit',
        meridiem: false
    },
    nowIndicator: true,
    eventDisplay: 'block',
    displayEventTime: true,
    displayEventEnd: true
}));

// Methods
const fetchEvents = async (start, end) => {
    loading.value = true;
    
    // Validate props
    if (!props.organizationId || !props.restaurantId) {
        console.error('Missing organizationId or restaurantId', props);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: 'Organization or Restaurant ID is missing',
            life: 5000
        });
        loading.value = false;
        return;
    }
    
    try {
        const response = await axios.get(
            `/organizations/${props.organizationId}/restaurants/${props.restaurantId}/reservations/calendar`,
            {
                params: {
                    start: start,
                    end: end
                }
            }
        );

        if (response.data.success) {
            events.value = response.data.data.events || [];
        }
    } catch (error) {
        console.error('Error fetching calendar events:', error);
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

const handleDatesSet = (dateInfo) => {
    const start = dateInfo.startStr.split('T')[0];
    const end = dateInfo.endStr.split('T')[0];
    fetchEvents(start, end);
};

const handleEventClick = (info) => {
    selectedEvent.value = info.event;
    showEventDialog.value = true;
};

const viewReservation = () => {
    if (selectedEvent.value) {
        emit('view-reservation', selectedEvent.value.extendedProps.reservation_id);
        showEventDialog.value = false;
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'confirmed': return 'success';
        case 'pending': return 'warning';
        case 'completed': return 'info';
        case 'paid': return 'success';
        case 'cancelled': return 'danger';
        case 'no_show': return 'danger';
        default: return 'secondary';
    }
};

const formatTime = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleTimeString(locale.value === 'ka' ? 'ka-GE' : 'en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Watch locale changes
watch(locale, () => {
    // Force calendar to re-render with new locale
    const currentView = calendarOptions.value.initialView;
    calendarOptions.value.initialView = 'timeGridDay';
    setTimeout(() => {
        calendarOptions.value.initialView = currentView;
    }, 0);
});

// Initial load
onMounted(() => {
    const today = new Date();
    const start = new Date(today.getFullYear(), today.getMonth(), 1);
    const end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    
    fetchEvents(
        start.toISOString().split('T')[0],
        end.toISOString().split('T')[0]
    );
});
</script>

<style scoped>
.reservation-calendar {
    padding: 1rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* FullCalendar Styles */
:deep(.fc) {
    font-family: inherit;
}

:deep(.fc-toolbar-title) {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
}

:deep(.fc-button) {
    background-color: #667eea !important;
    border-color: #667eea !important;
    text-transform: capitalize;
}

:deep(.fc-button:hover) {
    background-color: #5568d3 !important;
    border-color: #5568d3 !important;
}

:deep(.fc-button-active) {
    background-color: #4c51bf !important;
    border-color: #4c51bf !important;
}

:deep(.fc-event) {
    cursor: pointer;
    border: none;
    padding: 2px 4px;
    font-size: 0.85rem;
}

:deep(.fc-event:hover) {
    opacity: 0.9;
}

:deep(.fc-daygrid-event) {
    white-space: normal;
}

:deep(.fc-event-title) {
    font-weight: 500;
}

:deep(.fc-day-today) {
    background-color: #f0f4ff !important;
}

/* Event Details Dialog */
.event-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1rem 0;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.detail-label {
    font-weight: 600;
    color: #495057;
}

.detail-value {
    color: #212529;
}

@media (max-width: 768px) {
    .reservation-calendar {
        padding: 0.5rem;
    }

    :deep(.fc-toolbar) {
        flex-direction: column;
        gap: 0.5rem;
    }

    :deep(.fc-toolbar-chunk) {
        display: flex;
        justify-content: center;
    }
}
</style>

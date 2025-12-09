<template>
    <div class="reservation-calendar">
        <FullCalendar :key="calendarKey" ref="calendar" :options="calendarOptions" />
        
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
        default: null
    },
    restaurantId: {
        type: Number,
        default: null
    }
});

const emit = defineEmits(['view-reservation']);

// Data
const loading = ref(false);
const showEventDialog = ref(false);
const selectedEvent = ref(null);
const calendar = ref(null);
const calendarKey = ref(0); // Force re-render key

const STATUS_COLORS = {
    pending: '#facc15',
    confirmed: '#3b82f6',
    seated: '#8b5cf6',
    completed: '#10b981',
    cancelled: '#ef4444',
    no_show: '#f97316'
};

const FALLBACK_COLOR = '#64748b';

// Calendar Options - MUST be computed for reactivity
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
    events: fetchEventsForCalendar,
    eventClick: handleEventClick,
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
    displayEventEnd: true,
    editable: false,
    selectable: false
}));

// Methods
const fetchEventsForCalendar = async (fetchInfo, successCallback, failureCallback) => {
    try {
        // if (!props.organizationId || !props.restaurantId) {
        //     console.error('Missing org/rest IDs');
        //     failureCallback();
        //     return;
        // }

        const start = fetchInfo.startStr.split('T')[0];
        const end = fetchInfo.endStr.split('T')[0];

        console.log('📅 Fetching calendar events:', { start, end });

        const response = await axios.get(
            '/organizations/calendar/events', // Using the new standardized route (needs to be updated in web.php prefix if not global)
            // Actually, we put it under the prefix 'organizations/{organizationId}/restaurants/{restaurantId}/reservations' in web.php...
            // Wait, the plan was "Add /calendar/events route to expose ReservationController::calendar without ID requirements."
            // In web.php I added it inside the group. I should have added it OUTSIDE the group or constructed the URL correctly.
            // Let's check where I added it in web.php. I added it inside 'organizations/{organizationId}/restaurants/{restaurantId}/reservations'.
            // This defeats the purpose if I still need IDs in the URL.
            // I need to correct web.php first to put it outside, OR use a different route.
            // Actually, the previous 'reservations' fix was outside the group. I should probably move this one outside too in the next step.
            // For now, let's assume I'll move it to `/reservations/calendar/events` or similar global scope.
            // Let's use `'/calendar/events'` assuming I will fix web.php to have it globally.
             '/calendar/events',
            { params: { start, end } }
        );

        console.log('✅ Calendar response:', response.data);

        const payload = unwrapPayload(response.data);

        if (Array.isArray(payload)) {
            const normalizedEvents = payload.map((event) => normalizeEvent(event));

            console.log('📊 Events count:', normalizedEvents.length);
            successCallback(normalizedEvents);
        } else {
            console.warn('❌ Calendar payload is empty');
            failureCallback();
        }
    } catch (error) {
        console.error('❌ Calendar fetch error:', error);
        failureCallback();
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || 'Failed to load events',
            life: 5000
        });
    }
};

const handleDatesSet = (dateInfo) => {
    // This is not needed anymore since we use fetchEventsForCalendar
    // but keep it for compatibility
};

const handleEventClick = (info) => {
    selectedEvent.value = info.event;
    showEventDialog.value = true;
};

const viewReservation = () => {
    if (selectedEvent.value) {
        const reservationId = selectedEvent.value.extendedProps.reservation_id || selectedEvent.value.id;
        emit('view-reservation', reservationId);
        showEventDialog.value = false;
    }
};

const getStatusSeverity = (status) => {
    const normalized = normalizeStatus(status);
    switch (normalized) {
        case 'confirmed': return 'success';
        case 'pending': return 'warning';
        case 'completed': return 'info';
        case 'paid': return 'success';
        case 'cancelled': return 'danger';
        case 'no_show': return 'danger';
        case 'seated': return 'info';
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

const unwrapPayload = (payload) => {
    if (payload === null || payload === undefined) {
        return payload;
    }

    if (Array.isArray(payload)) {
        return payload;
    }

    if (payload?.events) {
        return unwrapPayload(payload.events);
    }

    if (Object.prototype.hasOwnProperty.call(payload, 'data')) {
        return unwrapPayload(payload.data);
    }

    return payload;
};

const normalizeEvent = (event) => {
    const source = unwrapPayload(event) || {};
    const extendedProps = source.extendedProps || {};
    const status = normalizeStatus(extendedProps.status || source.status);
    const color = source.backgroundColor || STATUS_COLORS[status] || FALLBACK_COLOR;

    const title = source.title || buildEventTitle(source, extendedProps);
    const start = source.start || buildEventDate(extendedProps.start || extendedProps.start_at || source.start_at);
    const end = source.end || buildEventDate(extendedProps.end || extendedProps.end_at || source.end_at);

    const reservationId = extendedProps.reservation_id || source.reservation_id || source.id;

    return {
        id: source.id || reservationId,
        title,
        start,
        end,
        backgroundColor: color,
        borderColor: source.borderColor || color,
        display: source.display || 'block',
        extendedProps: {
            ...extendedProps,
            reservation_id: reservationId,
            status,
            status_label: translateStatus(status),
            customer_name: extendedProps.customer_name || extractCustomerName(title),
            customer_phone: extendedProps.customer_phone || '-',
            customer_email: extendedProps.customer_email || null,
            guests_count: extendedProps.guests_count ?? extractGuestsCount(title),
            table_name: extendedProps.table_name || extendedProps.table_number || null,
            place_name: extendedProps.place_name || null,
            special_requests: extendedProps.special_requests || null
        }
    };
};

const normalizeStatus = (status) => {
    return (status || '')
        .toString()
        .toLowerCase()
        .replace('-', '_');
};

const translateStatus = (status) => {
    const labels = {
        pending: t('bookings.status.pending'),
        confirmed: t('bookings.status.confirmed'),
        seated: t('bookings.status.seated'),
        completed: t('bookings.status.completed'),
        cancelled: t('bookings.status.cancelled'),
        no_show: t('bookings.status.no_show')
    };
    return labels[status] || status;
};

const buildEventTitle = (event, props) => {
    const name = props.customer_name || props.customer || event.customer_name || '-';
    const guests = props.guests_count || props.party_size || extractGuestsCount(event.title) || '-';
    return `${name} - ${guests} ${t('bookings.table.guests').toLowerCase()}`;
};

const buildEventDate = (value) => {
    if (!value) {
        return null;
    }
    if (value instanceof Date) {
        return value;
    }
    return new Date(value);
};

const extractCustomerName = (title) => {
    if (!title || typeof title !== 'string') {
        return '-';
    }
    const [name] = title.split(' - ');
    return name || '-';
};

const extractGuestsCount = (title) => {
    if (!title || typeof title !== 'string') {
        return null;
    }
    const match = title.match(/(\d+)/);
    return match ? Number(match[1]) : null;
};

// Watch locale changes
watch(locale, () => {
    if (calendar.value) {
        const calendarApi = calendar.value.getApi();
        calendarApi.refetchEvents();
    }
});

// Don't need onMounted anymore - calendar will fetch automatically
onMounted(() => {
    console.log('🎯 ReservationCalendar mounted with IDs:', {
        orgId: props.organizationId,
        restId: props.restaurantId
    });
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

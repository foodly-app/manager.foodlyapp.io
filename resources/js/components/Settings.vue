<template>
    <div class="settings-page">
        <Toast />

        <div v-if="loading" class="loading-overlay">
            <ProgressSpinner />
        </div>

        <div class="page-header">
            <div>
                <h2 class="page-title">{{ $t('settings.title') }}</h2>
                <p class="page-subtitle">{{ $t('settings.subtitle') }}</p>
            </div>
            <div class="header-actions">
                <Button 
                    :label="$t('settings.actions.refresh')"
                    icon="pi pi-refresh"
                    severity="secondary"
                    outlined
                    @click="handleRefresh"
                    :loading="loading"
                />
                <Button 
                    :label="$t('settings.actions.reset')"
                    icon="pi pi-undo"
                    severity="secondary"
                    text
                    @click="resetForm"
                    :disabled="!hasChanges"
                />
                <Button 
                    :label="$t('common.save')"
                    icon="pi pi-check"
                    @click="saveSettings"
                    :disabled="disableSave"
                    :loading="saving"
                />
            </div>
        </div>

        <Message 
            v-if="!contextReady && !loading"
            severity="warn"
            class="context-warning"
        >
            {{ $t('settings.messages.contextMissing') }}
        </Message>

        <div v-if="contextReady" class="summary-cards">
            <Card class="summary-card">
                <template #content>
                    <div class="summary-icon" :class="form.reservationsEnabled ? 'success' : 'danger'">
                        <i :class="form.reservationsEnabled ? 'pi pi-check-circle' : 'pi pi-ban'"></i>
                    </div>
                    <div class="summary-texts">
                        <p class="summary-label">{{ $t('settings.fields.reservationsEnabled') }}</p>
                        <p class="summary-value">
                            {{ form.reservationsEnabled ? $t('settings.meta.active') : $t('settings.meta.inactive') }}
                        </p>
                    </div>
                </template>
            </Card>

            <Card class="summary-card">
                <template #content>
                    <div class="summary-icon" :class="form.onlinePaymentRequired ? 'info' : 'muted'">
                        <i :class="form.onlinePaymentRequired ? 'pi pi-credit-card' : 'pi pi-wallet'"></i>
                    </div>
                    <div class="summary-texts">
                        <p class="summary-label">{{ $t('settings.meta.onlinePayments') }}</p>
                        <p class="summary-value">
                            {{ form.onlinePaymentRequired ? $t('settings.meta.active') : $t('settings.meta.inactive') }}
                        </p>
                    </div>
                </template>
            </Card>

            <Card class="summary-card">
                <template #content>
                    <div class="summary-icon info">
                        <i class="pi pi-calendar"></i>
                    </div>
                    <div class="summary-texts">
                        <p class="summary-label">{{ $t('settings.meta.bookingWindow') }}</p>
                        <p class="summary-value">
                            {{ form.advanceBookingDays }} {{ $t('settings.units.days') }}
                        </p>
                    </div>
                </template>
            </Card>

            <Card class="summary-card">
                <template #content>
                    <div class="summary-icon warning">
                        <i class="pi pi-users"></i>
                    </div>
                    <div class="summary-texts">
                        <p class="summary-label">{{ $t('settings.meta.partySize') }}</p>
                        <p class="summary-value">
                            {{ form.minPartySize }} - {{ form.maxPartySize }} {{ $t('settings.units.guests') }}
                        </p>
                    </div>
                </template>
            </Card>
        </div>

        <div v-if="contextReady" class="settings-grid">
            <Card class="settings-card">
                <template #title>{{ $t('settings.sections.booking') }}</template>
                <template #content>
                    <div class="field-row">
                        <div class="field-meta">
                            <p class="field-title">{{ $t('settings.fields.reservationsEnabled') }}</p>
                            <p class="field-description">{{ $t('settings.fields.reservationsEnabledHint') }}</p>
                        </div>
                        <InputSwitch v-model="form.reservationsEnabled" :true-value="true" :false-value="false" />
                    </div>
                    <Divider />
                    <div class="field-row">
                        <div class="field-meta">
                            <p class="field-title">{{ $t('settings.fields.onlinePaymentRequired') }}</p>
                            <p class="field-description">{{ $t('settings.fields.onlinePaymentRequiredHint') }}</p>
                        </div>
                        <InputSwitch v-model="form.onlinePaymentRequired" :true-value="true" :false-value="false" />
                    </div>
                </template>
            </Card>

            <Card class="settings-card">
                <template #title>{{ $t('settings.sections.limits') }}</template>
                <template #content>
                    <div class="range-grid">
                        <div class="range-item">
                            <label for="advance-booking" class="field-title">{{ $t('settings.fields.advanceBookingDays') }}</label>
                            <p class="field-description">{{ $t('settings.fields.advanceBookingDaysHint') }}</p>
                            <InputNumber 
                                id="advance-booking"
                                v-model="form.advanceBookingDays"
                                :min="1"
                                :max="365"
                                :step="1"
                                inputId="advance_booking_days"
                                :useGrouping="false"
                                showButtons
                            />
                            <small v-if="errors.advanceBookingDays" class="error-text">{{ errors.advanceBookingDays }}</small>
                        </div>

                        <div class="range-item">
                            <label for="cancellation-hours" class="field-title">{{ $t('settings.fields.cancellationHours') }}</label>
                            <p class="field-description">{{ $t('settings.fields.cancellationHoursHint') }}</p>
                            <InputNumber 
                                id="cancellation-hours"
                                v-model="form.cancellationHours"
                                :min="0"
                                :max="240"
                                :step="1"
                                inputId="cancellation_hours"
                                :useGrouping="false"
                                showButtons
                            />
                            <small v-if="errors.cancellationHours" class="error-text">{{ errors.cancellationHours }}</small>
                        </div>
                    </div>

                    <div class="range-grid">
                        <div class="range-item">
                            <label for="min-party" class="field-title">{{ $t('settings.fields.minPartySize') }}</label>
                            <p class="field-description">{{ $t('settings.fields.minPartySizeHint') }}</p>
                            <InputNumber 
                                id="min-party"
                                v-model="form.minPartySize"
                                :min="1"
                                :max="form.maxPartySize"
                                :step="1"
                                inputId="min_party_size"
                                :useGrouping="false"
                                showButtons
                            />
                            <small v-if="errors.minPartySize" class="error-text">{{ errors.minPartySize }}</small>
                        </div>

                        <div class="range-item">
                            <label for="max-party" class="field-title">{{ $t('settings.fields.maxPartySize') }}</label>
                            <p class="field-description">{{ $t('settings.fields.maxPartySizeHint') }}</p>
                            <InputNumber 
                                id="max-party"
                                v-model="form.maxPartySize"
                                :min="form.minPartySize"
                                :max="200"
                                :step="1"
                                inputId="max_party_size"
                                :useGrouping="false"
                                showButtons
                            />
                            <small v-if="errors.maxPartySize" class="error-text">{{ errors.maxPartySize }}</small>
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="settings-card">
                <template #title>{{ $t('settings.sections.localization') }}</template>
                <template #content>
                    <div class="range-grid">
                        <div class="range-item">
                            <label for="timezone" class="field-title">{{ $t('settings.fields.timezone') }}</label>
                            <Dropdown 
                                id="timezone"
                                v-model="form.timezone"
                                :options="timezoneOptions"
                                optionLabel="label"
                                optionValue="value"
                                filter
                                :showClear="false"
                                class="w-full"
                                :placeholder="$t('settings.fields.timezonePlaceholder')"
                            />
                            <small v-if="errors.timezone" class="error-text">{{ errors.timezone }}</small>
                        </div>

                        <div class="range-item">
                            <label for="currency" class="field-title">{{ $t('settings.fields.currency') }}</label>
                            <Dropdown 
                                id="currency"
                                v-model="form.currency"
                                :options="currencyOptions"
                                optionLabel="label"
                                optionValue="value"
                                class="w-full"
                                :placeholder="$t('settings.fields.currencyPlaceholder')"
                            />
                            <small v-if="errors.currency" class="error-text">{{ errors.currency }}</small>
                        </div>

                        <div class="range-item">
                            <label for="language" class="field-title">{{ $t('settings.fields.language') }}</label>
                            <Dropdown 
                                id="language"
                                v-model="form.language"
                                :options="languageOptions"
                                optionLabel="label"
                                optionValue="value"
                                class="w-full"
                            />
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <Card v-if="contextReady" class="meta-card">
            <template #title>{{ $t('settings.sections.meta') }}</template>
            <template #content>
                <div class="meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">{{ $t('settings.meta.lastUpdated') }}</span>
                        <span class="meta-value">{{ formattedLastUpdated }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Organization ID</span>
                        <Tag :value="`#${organizationId}`" severity="info" />
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Restaurant ID</span>
                        <Tag :value="`#${restaurantId}`" severity="info" />
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

import Button from 'primevue/button';
import Card from 'primevue/card';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import InputSwitch from 'primevue/inputswitch';
import Message from 'primevue/message';
import ProgressSpinner from 'primevue/progressspinner';
import Tag from 'primevue/tag';
import Toast from 'primevue/toast';
import Divider from 'primevue/divider';

const { t, locale } = useI18n();
const toast = useToast();

const loading = ref(false);
const saving = ref(false);
const organizationId = ref(null);
const restaurantId = ref(null);
const rawSettings = ref({});
const rawSettingKeys = ref([]);
const originalForm = ref(null);
const metadata = reactive({
    updatedAt: null,
    lastFetchedAt: null
});

const form = reactive({
    reservationsEnabled: true,
    onlinePaymentRequired: false,
    advanceBookingDays: 30,
    cancellationHours: 24,
    minPartySize: 1,
    maxPartySize: 12,
    timezone: 'Asia/Tbilisi',
    currency: 'GEL',
    language: 'ka'
});

const errors = reactive({
    advanceBookingDays: '',
    cancellationHours: '',
    minPartySize: '',
    maxPartySize: '',
    timezone: '',
    currency: ''
});

const timezoneOptions = [
    { label: 'Asia/Tbilisi (GMT+4)', value: 'Asia/Tbilisi' },
    { label: 'Europe/Berlin (GMT+1)', value: 'Europe/Berlin' },
    { label: 'Europe/Moscow (GMT+3)', value: 'Europe/Moscow' },
    { label: 'UTC (GMT+0)', value: 'UTC' },
    { label: 'Asia/Dubai (GMT+4)', value: 'Asia/Dubai' }
];

const currencyOptions = [
    { label: 'GEL - Georgian Lari', value: 'GEL' },
    { label: 'USD - US Dollar', value: 'USD' },
    { label: 'EUR - Euro', value: 'EUR' }
];

const languageOptions = [
    { label: 'ქართული', value: 'ka' },
    { label: 'English', value: 'en' }
];

const contextReady = computed(() => Boolean(organizationId.value && restaurantId.value));

const hasChanges = computed(() => {
    if (!originalForm.value) {
        return false;
    }

    return Object.keys(originalForm.value).some((key) => form[key] !== originalForm.value[key]);
});

const disableSave = computed(() => saving.value || !hasChanges.value || !contextReady.value);

const formattedLastUpdated = computed(() => {
    const reference = metadata.updatedAt ?? metadata.lastFetchedAt;
    if (!reference) {
        return t('settings.meta.never');
    }

    const date = reference instanceof Date ? reference : new Date(reference);
    if (Number.isNaN(date.getTime())) {
        return t('settings.meta.never');
    }

    return new Intl.DateTimeFormat(locale.value, {
        dateStyle: 'medium',
        timeStyle: 'short'
    }).format(date);
});

const resetErrors = () => {
    errors.advanceBookingDays = '';
    errors.cancellationHours = '';
    errors.minPartySize = '';
    errors.maxPartySize = '';
    errors.timezone = '';
    errors.currency = '';
};

const normalizeSettings = (data = {}) => ({
    reservationsEnabled: data.reservation_enabled ?? data.booking_enabled ?? form.reservationsEnabled,
    onlinePaymentRequired: data.online_payment_required ?? form.onlinePaymentRequired,
    advanceBookingDays: data.advance_booking_days ?? form.advanceBookingDays,
    cancellationHours: data.cancellation_hours ?? data.cancellation_policy?.hours ?? form.cancellationHours,
    minPartySize: data.min_party_size ?? form.minPartySize,
    maxPartySize: data.max_party_size ?? form.maxPartySize,
    timezone: data.timezone ?? form.timezone,
    currency: data.currency ?? form.currency,
    language: data.language ?? form.language
});

const applySettings = (data = {}) => {
    rawSettings.value = data || {};
    rawSettingKeys.value = Object.keys(rawSettings.value);

    const normalized = normalizeSettings(rawSettings.value);

    Object.assign(form, normalized);
    originalForm.value = { ...normalized };

    metadata.updatedAt = rawSettings.value.updated_at ?? rawSettings.value.updatedAt ?? null;
    metadata.lastFetchedAt = new Date().toISOString();
};

const ensureContext = async () => {
    const storedOrgId = localStorage.getItem('organizationId');
    const storedRestaurantId = localStorage.getItem('restaurantId');

    if (storedOrgId) {
        organizationId.value = Number(storedOrgId);
    }
    if (storedRestaurantId) {
        restaurantId.value = Number(storedRestaurantId);
    }

    if (organizationId.value && restaurantId.value) {
        return true;
    }

    try {
        const dashboardResponse = await axios.get('/auth/initial-dashboard');
        if (dashboardResponse.data?.success) {
            const payload = dashboardResponse.data.data || {};
            if (payload.organization?.id) {
                organizationId.value = Number(payload.organization.id);
                localStorage.setItem('organizationId', organizationId.value.toString());
            }
            if (payload.restaurant?.id) {
                restaurantId.value = Number(payload.restaurant.id);
                localStorage.setItem('restaurantId', restaurantId.value.toString());
            }
        }
    } catch (error) {
        console.error('Failed to resolve context:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || error.message || t('settings.messages.loadFailed'),
            life: 4000
        });
    }

    return Boolean(organizationId.value && restaurantId.value);
};

const fetchSettings = async () => {
    loading.value = true;

    try {
        const hasContext = await ensureContext();
        if (!hasContext) {
            return;
        }

        const response = await axios.get(`/organizations/${organizationId.value}/restaurants/${restaurantId.value}/settings`);
        const payload = response.data?.data ?? response.data ?? {};
        applySettings(payload);
    } catch (error) {
        console.error('Settings fetch error:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('settings.messages.loadFailed'),
            life: 4000
        });
    } finally {
        loading.value = false;
    }
};

const validateForm = () => {
    resetErrors();
    let valid = true;

    if (form.advanceBookingDays === null || form.advanceBookingDays < 1) {
        errors.advanceBookingDays = t('settings.validation.advanceBookingDaysMin');
        valid = false;
    }

    if (form.cancellationHours === null || form.cancellationHours < 0) {
        errors.cancellationHours = t('settings.validation.cancellationHoursMin');
        valid = false;
    }

    if (form.minPartySize === null || form.minPartySize < 1) {
        errors.minPartySize = t('settings.validation.minPartySizeMin');
        valid = false;
    }

    if (form.maxPartySize === null || form.maxPartySize < form.minPartySize) {
        errors.maxPartySize = t('settings.validation.maxPartySizeMin');
        valid = false;
    }

    if (!form.timezone) {
        errors.timezone = t('settings.validation.timezoneRequired');
        valid = false;
    }

    if (!form.currency) {
        errors.currency = t('settings.validation.currencyRequired');
        valid = false;
    }

    return valid;
};

const buildPayload = () => {
    const keys = new Set(rawSettingKeys.value);

    const payload = {
        booking_enabled: form.reservationsEnabled,
        advance_booking_days: form.advanceBookingDays
    };

    if (keys.has('reservation_enabled') || !keys.has('booking_enabled')) {
        payload.reservation_enabled = form.reservationsEnabled;
    }

    if (keys.has('online_payment_required') || keys.size === 0) {
        payload.online_payment_required = form.onlinePaymentRequired;
    }

    if (keys.has('cancellation_hours') || keys.size === 0) {
        payload.cancellation_hours = form.cancellationHours;
    }

    if (keys.has('min_party_size') || keys.size === 0) {
        payload.min_party_size = form.minPartySize;
    }

    if (keys.has('max_party_size') || keys.size === 0) {
        payload.max_party_size = form.maxPartySize;
    }

    if (keys.has('timezone') || keys.size === 0) {
        payload.timezone = form.timezone;
    }

    if (keys.has('currency') || keys.size === 0) {
        payload.currency = form.currency;
    }

    if (keys.has('language') || keys.size === 0) {
        payload.language = form.language;
    }

    return payload;
};

const saveSettings = async () => {
    if (!contextReady.value) {
        return;
    }

    if (!validateForm()) {
        return;
    }

    saving.value = true;

    try {
        const payload = buildPayload();
        const response = await axios.put(
            `/organizations/${organizationId.value}/restaurants/${restaurantId.value}/settings`,
            payload
        );

        const updated = response.data?.data ?? response.data ?? {};
        applySettings(updated);

        toast.add({
            severity: 'success',
            summary: t('common.success'),
            detail: t('settings.messages.updateSuccess'),
            life: 3000
        });
    } catch (error) {
        console.error('Settings update error:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('settings.messages.updateFailed'),
            life: 4000
        });
    } finally {
        saving.value = false;
    }
};

const resetForm = () => {
    if (!originalForm.value) {
        return;
    }

    Object.assign(form, originalForm.value);
    resetErrors();
};

const handleRefresh = () => {
    if (!loading.value) {
        fetchSettings();
    }
};

watch(() => form.minPartySize, (value) => {
    if (value === null) {
        return;
    }
    if (value < 1) {
        form.minPartySize = 1;
    }
    if (form.maxPartySize !== null && value > form.maxPartySize) {
        form.maxPartySize = value;
    }
});

watch(() => form.maxPartySize, (value) => {
    if (value === null) {
        return;
    }
    if (value < form.minPartySize) {
        form.maxPartySize = form.minPartySize;
    }
});

watch(() => form.advanceBookingDays, (value) => {
    if (value === null) {
        return;
    }
    if (value < 1) {
        form.advanceBookingDays = 1;
    }
});

watch(() => form.cancellationHours, (value) => {
    if (value === null) {
        return;
    }
    if (value < 0) {
        form.cancellationHours = 0;
    }
});

onMounted(() => {
    fetchSettings();
});
</script>

<style scoped>
.settings-page {
    padding: 1.5rem;
    position: relative;
}

.loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    gap: 1rem;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.page-subtitle {
    color: #6b7280;
    margin-top: 0.25rem;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.context-warning {
    margin-bottom: 1.5rem;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.summary-card {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.summary-card :deep(.p-card-body) {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
}

.summary-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.summary-icon.success {
    background: rgba(16, 185, 129, 0.15);
    color: #047857;
}

.summary-icon.danger {
    background: rgba(239, 68, 68, 0.15);
    color: #b91c1c;
}

.summary-icon.info {
    background: rgba(59, 130, 246, 0.15);
    color: #1d4ed8;
}

.summary-icon.warning {
    background: rgba(234, 179, 8, 0.15);
    color: #b45309;
}

.summary-icon.muted {
    background: rgba(107, 114, 128, 0.15);
    color: #4b5563;
}

.summary-label {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.summary-value {
    margin: 0;
    font-weight: 600;
    color: #1f2937;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
}

.settings-card :deep(.p-card-body) {
    padding: 1.5rem;
}

.field-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem 0;
}

.field-meta {
    flex: 1;
}

.field-title {
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.field-description {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.range-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.25rem;
    margin-top: 1rem;
}

.range-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.error-text {
    color: #dc2626;
    font-size: 0.75rem;
}

.meta-card {
    margin-top: 1.5rem;
}

.meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.meta-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.meta-value {
    font-weight: 600;
    color: #1f2937;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>

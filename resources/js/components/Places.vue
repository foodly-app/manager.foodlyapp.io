<template>
    <div class="places-page">
        <Toast />
        
        <!-- Loading Overlay -->
        <div v-if="loading" class="loading-overlay">
            <ProgressSpinner />
        </div>

        <!-- Header with Actions -->
        <div class="page-header">
            <div class="header-left">
                <h2 class="page-title">{{ $t('places.title') }}</h2>
                <p class="page-subtitle">{{ $t('places.subtitle') }}</p>
            </div>
            <div class="header-right">
                <Button 
                    :label="$t('places.actions.newPlace')" 
                    icon="pi pi-plus" 
                    @click="openCreateDialog"
                />
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <Card class="stat-card">
                <template #content>
                    <div class="stat-content">
                        <div class="stat-icon total">
                            <i class="pi pi-map-marker"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-value">{{ totalPlaces }}</div>
                            <div class="stat-label">{{ $t('places.stats.total') }}</div>
                        </div>
                    </div>
                </template>
            </Card>
            
            <Card class="stat-card">
                <template #content>
                    <div class="stat-content">
                        <div class="stat-icon active">
                            <i class="pi pi-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-value">{{ activePlaces }}</div>
                            <div class="stat-label">{{ $t('places.stats.active') }}</div>
                        </div>
                    </div>
                </template>
            </Card>
            
            <Card class="stat-card">
                <template #content>
                    <div class="stat-content">
                        <div class="stat-icon tables">
                            <i class="pi pi-table"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-value">{{ totalTables }}</div>
                            <div class="stat-label">{{ $t('places.stats.totalTables') }}</div>
                        </div>
                    </div>
                </template>
            </Card>
            
            <Card class="stat-card">
                <template #content>
                    <div class="stat-content">
                        <div class="stat-icon capacity">
                            <i class="pi pi-users"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-value">{{ totalCapacity }}</div>
                            <div class="stat-label">{{ $t('places.stats.totalCapacity') }}</div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Places List -->
        <Card class="places-list-card">
            <template #content>
                <div class="table-controls">
                    <InputText 
                        v-model="searchQuery" 
                        :placeholder="$t('places.filters.search')"
                        class="search-input"
                    />
                </div>

                <DataTable 
                    :value="filteredPlaces" 
                    :paginator="true" 
                    :rows="10"
                    :loading="loading"
                    class="places-table"
                    :rowHover="true"
                    responsiveLayout="scroll"
                >
                    <template #empty>
                        <div class="empty-state">
                            <i class="pi pi-map-marker"></i>
                            <p>{{ $t('places.empty') }}</p>
                        </div>
                    </template>

                    <Column field="id" :header="$t('places.columns.id')" style="width: 80px" />
                    <Column field="name" :header="$t('places.columns.name')">
                        <template #body="slotProps">
                            <div class="place-name">
                                <i class="pi pi-map-marker"></i>
                                <span>{{ slotProps.data.name }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column field="description" :header="$t('places.columns.description')" />
                    <Column field="tableCount" :header="$t('places.columns.tables')" style="width: 120px">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.tableCount || 0" severity="info" />
                        </template>
                    </Column>
                    <Column field="capacity" :header="$t('places.columns.capacity')" style="width: 120px">
                        <template #body="slotProps">
                            <div class="capacity-cell">
                                <i class="pi pi-users"></i>
                                <span>{{ slotProps.data.capacity || 0 }}</span>
                            </div>
                        </template>
                    </Column>
                    <Column field="status" :header="$t('places.columns.status')" style="width: 120px">
                        <template #body="slotProps">
                            <Tag 
                                :value="$t(`places.status.${slotProps.data.status}`)" 
                                :severity="getStatusSeverity(slotProps.data.status)"
                            />
                        </template>
                    </Column>
                    <Column :header="$t('places.columns.actions')" style="width: 180px">
                        <template #body="slotProps">
                            <div class="action-buttons">
                                <Button 
                                    icon="pi pi-eye" 
                                    class="p-button-rounded p-button-text p-button-info" 
                                    @click="viewPlace(slotProps.data)"
                                    v-tooltip.top="$t('places.actions.view')"
                                />
                                <Button 
                                    icon="pi pi-pencil" 
                                    class="p-button-rounded p-button-text p-button-warning" 
                                    @click="editPlace(slotProps.data)"
                                    v-tooltip.top="$t('places.actions.edit')"
                                />
                                <Button 
                                    icon="pi pi-trash" 
                                    class="p-button-rounded p-button-text p-button-danger" 
                                    @click="confirmDelete(slotProps.data)"
                                    v-tooltip.top="$t('places.actions.delete')"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <!-- Create/Edit Dialog -->
        <Dialog 
            v-model:visible="showDialog" 
            :header="isEditing ? $t('places.dialog.editTitle') : $t('places.dialog.createTitle')"
            :modal="true"
            :style="{ width: '600px' }"
            :closable="true"
            class="place-dialog"
        >
            <div class="dialog-content">
                <div class="form-field">
                    <label for="name">{{ $t('places.form.name') }}</label>
                    <InputText 
                        id="name"
                        v-model="formData.name" 
                        :placeholder="$t('places.form.namePlaceholder')"
                        class="w-full"
                    />
                </div>

                <div class="form-field">
                    <label for="description">{{ $t('places.form.description') }}</label>
                    <Textarea 
                        id="description"
                        v-model="formData.description" 
                        :placeholder="$t('places.form.descriptionPlaceholder')"
                        rows="3"
                        class="w-full"
                    />
                </div>

                <div class="form-field">
                    <label for="capacity">{{ $t('places.form.capacity') }}</label>
                    <InputNumber 
                        id="capacity"
                        v-model="formData.capacity" 
                        :min="0"
                        class="w-full"
                    />
                </div>

                <div class="form-field">
                    <label for="status">{{ $t('places.form.status') }}</label>
                    <Dropdown 
                        id="status"
                        v-model="formData.status" 
                        :options="statusOptions"
                        optionLabel="label"
                        optionValue="value"
                        :placeholder="$t('places.form.selectStatus')"
                        class="w-full"
                    />
                </div>
            </div>

            <template #footer>
                <Button 
                    :label="$t('common.cancel')" 
                    icon="pi pi-times" 
                    class="p-button-text" 
                    @click="closeDialog"
                />
                <Button 
                    :label="isEditing ? $t('common.update') : $t('common.create')" 
                    icon="pi pi-check" 
                    @click="savePlace"
                    :loading="saving"
                />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog 
            v-model:visible="showViewDialog" 
            :header="$t('places.dialog.viewTitle')"
            :modal="true"
            :style="{ width: '600px' }"
            :closable="true"
        >
            <div v-if="selectedPlace" class="place-details">
                <div class="detail-row">
                    <span class="detail-label">{{ $t('places.columns.id') }}:</span>
                    <span class="detail-value">{{ selectedPlace.id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('places.columns.name') }}:</span>
                    <span class="detail-value">{{ selectedPlace.name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('places.columns.description') }}:</span>
                    <span class="detail-value">{{ selectedPlace.description || '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('places.columns.tables') }}:</span>
                    <span class="detail-value">{{ selectedPlace.tableCount || 0 }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('places.columns.capacity') }}:</span>
                    <span class="detail-value">{{ selectedPlace.capacity || 0 }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ $t('places.columns.status') }}:</span>
                    <Tag 
                        :value="$t(`places.status.${selectedPlace.status}`)" 
                        :severity="getStatusSeverity(selectedPlace.status)"
                    />
                </div>
            </div>

            <template #footer>
                <Button 
                    :label="$t('common.close')" 
                    icon="pi pi-times" 
                    class="p-button-text" 
                    @click="showViewDialog = false"
                />
            </template>
        </Dialog>

        <!-- Delete Confirmation -->
        <Dialog 
            v-model:visible="showDeleteDialog" 
            :header="$t('places.dialog.deleteTitle')"
            :modal="true"
            :style="{ width: '450px' }"
        >
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle"></i>
                <p>{{ $t('places.dialog.deleteMessage') }}</p>
                <p class="place-name-highlight">{{ placeToDelete?.name }}</p>
            </div>

            <template #footer>
                <Button 
                    :label="$t('common.cancel')" 
                    icon="pi pi-times" 
                    class="p-button-text" 
                    @click="showDeleteDialog = false"
                />
                <Button 
                    :label="$t('common.delete')" 
                    icon="pi pi-trash" 
                    class="p-button-danger" 
                    @click="deletePlace"
                    :loading="deleting"
                />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Toast from 'primevue/toast';
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Tag from 'primevue/tag';
import ProgressSpinner from 'primevue/progressspinner';

const { t } = useI18n();
const toast = useToast();

// Data
const places = ref([]);
const loading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const searchQuery = ref('');

// Dialogs
const showDialog = ref(false);
const showViewDialog = ref(false);
const showDeleteDialog = ref(false);
const isEditing = ref(false);
const selectedPlace = ref(null);
const placeToDelete = ref(null);

// Form Data
const formData = ref({
    name: '',
    description: '',
    capacity: 0,
    status: 'active'
});

// Status Options
const statusOptions = computed(() => [
    { label: t('places.status.active'), value: 'active' },
    { label: t('places.status.inactive'), value: 'inactive' }
]);

// Computed Stats
const totalPlaces = computed(() => places.value.length);
const activePlaces = computed(() => places.value.filter(p => p.status === 'active').length);
const totalTables = computed(() => places.value.reduce((sum, p) => sum + (p.tableCount || 0), 0));
const totalCapacity = computed(() => places.value.reduce((sum, p) => sum + (p.capacity || 0), 0));

// Filtered Places
const filteredPlaces = computed(() => {
    if (!searchQuery.value) return places.value;
    
    const query = searchQuery.value.toLowerCase();
    return places.value.filter(place => 
        place.name?.toLowerCase().includes(query) ||
        place.description?.toLowerCase().includes(query)
    );
});

// Methods
const fetchPlaces = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/places');
        
        console.log('Places API Response:', response.data);
        
        // Process places data to add tableCount and capacity
        const placesData = response.data.success ? response.data.data : response.data;
        places.value = (placesData || []).map(place => {
            const tables = place.tables || [];
            return {
                ...place,
                tableCount: tables.length,
                capacity: tables.reduce((sum, table) => sum + (table.capacity || table.seats || 0), 0)
            };
        });
    } catch (error) {
        console.error('Error fetching places:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('places.errors.fetchFailed'),
            life: 5000
        });
        places.value = []; // Set empty array on error
    } finally {
        loading.value = false;
    }
};

const openCreateDialog = () => {
    isEditing.value = false;
    formData.value = {
        name: '',
        description: '',
        capacity: 0,
        status: 'active'
    };
    showDialog.value = true;
};

const editPlace = (place) => {
    isEditing.value = true;
    selectedPlace.value = place;
    formData.value = {
        name: place.name,
        description: place.description,
        capacity: place.capacity,
        status: place.status
    };
    showDialog.value = true;
};

const viewPlace = (place) => {
    selectedPlace.value = place;
    showViewDialog.value = true;
};

const closeDialog = () => {
    showDialog.value = false;
    formData.value = {
        name: '',
        description: '',
        capacity: 0,
        status: 'active'
    };
    selectedPlace.value = null;
};

const savePlace = async () => {
    if (!formData.value.name) {
        toast.add({
            severity: 'warn',
            summary: t('common.warning'),
            detail: t('places.validation.nameRequired'),
            life: 3000
        });
        return;
    }

    saving.value = true;
    try {
        // Get organization and restaurant IDs from initial dashboard
        const dashboardResponse = await axios.get('/auth/initial-dashboard');
        const organizationId = dashboardResponse.data.data.organization.id;
        const restaurantId = dashboardResponse.data.data.restaurant.id;

        const url = isEditing.value
            ? `/organizations/${organizationId}/restaurants/${restaurantId}/places/${selectedPlace.value.id}`
            : `/organizations/${organizationId}/restaurants/${restaurantId}/places`;

        const method = isEditing.value ? 'put' : 'post';
        const response = await axios[method](url, formData.value);

        if (response.data.success) {
            toast.add({
                severity: 'success',
                summary: t('common.success'),
                detail: isEditing.value ? t('places.messages.updateSuccess') : t('places.messages.createSuccess'),
                life: 3000
            });
            
            closeDialog();
            await fetchPlaces();
        } else {
            throw new Error(response.data.message);
        }
    } catch (error) {
        console.error('Error saving place:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || (isEditing.value ? t('places.errors.updateFailed') : t('places.errors.createFailed')),
            life: 5000
        });
    } finally {
        saving.value = false;
    }
};

const confirmDelete = (place) => {
    placeToDelete.value = place;
    showDeleteDialog.value = true;
};

const deletePlace = async () => {
    deleting.value = true;
    try {
        // Get organization and restaurant IDs from initial dashboard
        const dashboardResponse = await axios.get('/auth/initial-dashboard');
        const organizationId = dashboardResponse.data.data.organization.id;
        const restaurantId = dashboardResponse.data.data.restaurant.id;

        const response = await axios.delete(
            `/organizations/${organizationId}/restaurants/${restaurantId}/places/${placeToDelete.value.id}`
        );

        if (response.data.success) {
            toast.add({
                severity: 'success',
                summary: t('common.success'),
                detail: t('places.messages.deleteSuccess'),
                life: 3000
            });
            
            showDeleteDialog.value = false;
            placeToDelete.value = null;
            await fetchPlaces();
        } else {
            throw new Error(response.data.message);
        }
    } catch (error) {
        console.error('Error deleting place:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('places.errors.deleteFailed'),
            life: 5000
        });
    } finally {
        deleting.value = false;
    }
};

const getStatusSeverity = (status) => {
    switch (status) {
        case 'active': return 'success';
        case 'inactive': return 'warning';
        default: return 'info';
    }
};

// Lifecycle
onMounted(() => {
    fetchPlaces();
});
</script>

<style scoped>
.places-page {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.page-subtitle {
    color: #6c757d;
    margin: 0.5rem 0 0 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.stat-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.active { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.stat-icon.tables { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); }
.stat-icon.capacity { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }

.stat-details {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
}

.stat-label {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.places-list-card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table-controls {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.search-input {
    flex: 1;
    max-width: 400px;
}

.place-name {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.place-name i {
    color: #667eea;
}

.capacity-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.dialog-content {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 1rem 0;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-field label {
    font-weight: 600;
    color: #2c3e50;
}

.place-details {
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

.confirmation-content {
    text-align: center;
    padding: 1rem;
}

.confirmation-content i {
    font-size: 4rem;
    color: #f59e0b;
    margin-bottom: 1rem;
}

.confirmation-content p {
    margin: 0.5rem 0;
    color: #6c757d;
}

.place-name-highlight {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .places-page {
        padding: 1rem;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<template>
    <div class="tables-page">
        <Toast />
        
        <!-- Loading Overlay -->
        <div v-if="loading" class="loading-overlay">
            <ProgressSpinner />
        </div>

        <!-- Header with Actions -->
        <div class="page-header">
            <div class="header-left">
                <!-- <h2 class="page-title">{{ $t('tables.title') }}</h2> -->
                <p class="page-subtitle">{{ $t('tables.subtitle') }}</p>
            </div>
            <div class="header-right">
                <Button 
                    :label="$t('tables.actions.newTable')" 
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
                            <i class="pi pi-table"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-value">{{ totalTables }}</div>
                            <div class="stat-label">{{ $t('tables.stats.total') }}</div>
                        </div>
                    </div>
                </template>
            </Card>
            
            <Card class="stat-card">
                <template #content>
                    <div class="stat-content">
                        <div class="stat-icon available">
                            <i class="pi pi-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-value">{{ availableTables }}</div>
                            <div class="stat-label">{{ $t('tables.stats.available') }}</div>
                        </div>
                    </div>
                </template>
            </Card>
            
            <Card class="stat-card">
                <template #content>
                    <div class="stat-content">
                        <div class="stat-icon occupied">
                            <i class="pi pi-users"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-value">{{ occupiedTables }}</div>
                            <div class="stat-label">{{ $t('tables.stats.occupied') }}</div>
                        </div>
                    </div>
                </template>
            </Card>
            
            <Card class="stat-card">
                <template #content>
                    <div class="stat-content">
                        <div class="stat-icon capacity">
                            <i class="pi pi-user"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-value">{{ totalCapacity }}</div>
                            <div class="stat-label">{{ $t('tables.stats.totalCapacity') }}</div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- View Switcher -->
        <Card class="view-card">
            <template #content>
                <div class="view-controls">
                    <SelectButton v-model="viewMode" :options="viewOptions" optionLabel="label" optionValue="value">
                        <template #option="slotProps">
                            <i :class="slotProps.option.icon"></i>
                            <span class="ml-2">{{ slotProps.option.label }}</span>
                        </template>
                    </SelectButton>
                    
                    <div class="filter-controls">
                        <InputText 
                            v-model="searchQuery" 
                            :placeholder="$t('tables.filters.search')"
                            class="search-input"
                        >
                            <template #prefix>
                                <i class="pi pi-search"></i>
                            </template>
                        </InputText>
                        
                        <Dropdown 
                            v-model="statusFilter" 
                            :options="statusFilterOptions" 
                            optionLabel="label"
                            optionValue="value"
                            :placeholder="$t('tables.filters.status')"
                            showClear
                        />
                    </div>
                </div>
            </template>
        </Card>

        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="tables-grid">
            <Card 
                v-for="table in filteredTables" 
                :key="table.id" 
                class="table-card"
                :class="{ 'occupied': table.status === 'occupied' }"
            >
                <template #content>
                    <div class="table-card-header">
                        <div class="table-info">
                            <h3 class="table-name">{{ table.name }}</h3>
                            <Tag 
                                :value="translateStatus(table.status)" 
                                :severity="getStatusSeverity(table.status)"
                                class="status-tag"
                            />
                        </div>
                        <div class="table-actions">
                            <Button 
                                icon="pi pi-pencil" 
                                severity="info" 
                                text 
                                rounded
                                v-tooltip.top="$t('common.edit')"
                                @click="openEditDialog(table)"
                            />
                            <Button 
                                icon="pi pi-trash" 
                                severity="danger" 
                                text 
                                rounded
                                v-tooltip.top="$t('common.delete')"
                                @click="confirmDelete(table)"
                            />
                        </div>
                    </div>
                    
                    <div class="table-details">
                        <div class="detail-item">
                            <i class="pi pi-user"></i>
                            <span>{{ table.capacity }} {{ $t('tables.capacity') }}</span>
                        </div>
                        <div class="detail-item">
                            <i class="pi pi-map-marker"></i>
                            <span>{{ table.location || '-' }}</span>
                        </div>
                    </div>
                    
                    <div v-if="table.description" class="table-description">
                        {{ table.description }}
                    </div>
                </template>
            </Card>
        </div>

        <!-- List View -->
        <Card v-else class="table-card">
            <template #content>
                <DataTable 
                    :value="filteredTables" 
                    :paginator="true" 
                    :rows="10"
                    :rowsPerPageOptions="[10, 25, 50]"
                    :loading="loading"
                    stripedRows
                    showGridlines
                >
                    <Column field="name" :header="$t('tables.table.name')" sortable></Column>
                    <Column field="capacity" :header="$t('tables.table.capacity')" sortable style="width: 120px"></Column>
                    <Column field="location" :header="$t('tables.table.location')" sortable></Column>
                    <Column field="status" :header="$t('tables.table.status')" sortable style="width: 150px">
                        <template #body="slotProps">
                            <Tag 
                                :value="translateStatus(slotProps.data.status)" 
                                :severity="getStatusSeverity(slotProps.data.status)"
                            />
                        </template>
                    </Column>
                    <Column field="description" :header="$t('tables.table.description')"></Column>
                    <Column :header="$t('common.actions')" style="width: 150px">
                        <template #body="slotProps">
                            <div class="action-buttons">
                                <Button 
                                    icon="pi pi-pencil" 
                                    severity="info" 
                                    text 
                                    rounded
                                    v-tooltip.top="$t('common.edit')"
                                    @click="openEditDialog(slotProps.data)"
                                />
                                <Button 
                                    icon="pi pi-trash" 
                                    severity="danger" 
                                    text 
                                    rounded
                                    v-tooltip.top="$t('common.delete')"
                                    @click="confirmDelete(slotProps.data)"
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
            :header="dialogMode === 'create' ? $t('tables.actions.newTable') : $t('tables.actions.editTable')"
            :style="{ width: '500px' }"
            modal
        >
            <div class="form-grid">
                <div class="form-field">
                    <label for="name">{{ $t('tables.form.name') }} *</label>
                    <InputText 
                        id="name" 
                        v-model="formData.name" 
                        :placeholder="$t('tables.form.namePlaceholder')"
                    />
                </div>
                
                <div class="form-field">
                    <label for="capacity">{{ $t('tables.form.capacity') }} *</label>
                    <InputNumber 
                        id="capacity" 
                        v-model="formData.capacity" 
                        :min="1" 
                        :max="20"
                        showButtons
                    />
                </div>
                
                <div class="form-field">
                    <label for="location">{{ $t('tables.form.location') }}</label>
                    <InputText 
                        id="location" 
                        v-model="formData.location" 
                        :placeholder="$t('tables.form.locationPlaceholder')"
                    />
                </div>
                
                <div class="form-field">
                    <label for="status">{{ $t('tables.form.status') }}</label>
                    <Dropdown 
                        id="status" 
                        v-model="formData.status" 
                        :options="statusOptions" 
                        optionLabel="label"
                        optionValue="value"
                    />
                </div>
                
                <div class="form-field full-width">
                    <label for="description">{{ $t('tables.form.description') }}</label>
                    <Textarea 
                        id="description" 
                        v-model="formData.description" 
                        :placeholder="$t('tables.form.descriptionPlaceholder')"
                        rows="3"
                    />
                </div>
            </div>
            
            <template #footer>
                <Button 
                    :label="$t('common.cancel')" 
                    severity="secondary" 
                    @click="showDialog = false"
                />
                <Button 
                    :label="$t('common.save')" 
                    @click="saveTable"
                    :loading="saving"
                />
            </template>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog 
            v-model:visible="showDeleteDialog" 
            :header="$t('tables.deleteDialog.title')"
            :style="{ width: '450px' }"
            modal
        >
            <div class="flex items-center gap-3">
                <i class="pi pi-exclamation-triangle text-3xl text-red-500"></i>
                <span>{{ $t('tables.deleteDialog.message', { name: selectedTable?.name }) }}</span>
            </div>
            
            <template #footer>
                <Button 
                    :label="$t('common.cancel')" 
                    severity="secondary" 
                    @click="showDeleteDialog = false"
                />
                <Button 
                    :label="$t('common.delete')" 
                    severity="danger" 
                    @click="deleteTable"
                    :loading="deleting"
                />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import Tag from 'primevue/tag';
import SelectButton from 'primevue/selectbutton';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Tooltip from 'primevue/tooltip';

const toast = useToast();
const { t } = useI18n();

const loading = ref(true);
const saving = ref(false);
const deleting = ref(false);
const tables = ref([]);
const selectedTable = ref(null);
const showDialog = ref(false);
const showDeleteDialog = ref(false);
const dialogMode = ref('create');
const viewMode = ref('grid');
const searchQuery = ref('');
const statusFilter = ref(null);

const formData = ref({
    name: '',
    capacity: 4,
    location: '',
    status: 'available',
    description: ''
});

const viewOptions = computed(() => [
    { label: t('tables.views.grid'), value: 'grid', icon: 'pi pi-th-large' },
    { label: t('tables.views.list'), value: 'list', icon: 'pi pi-list' }
]);

const statusOptions = computed(() => [
    { label: t('tables.status.available'), value: 'available' },
    { label: t('tables.status.occupied'), value: 'occupied' },
    { label: t('tables.status.reserved'), value: 'reserved' },
    { label: t('tables.status.maintenance'), value: 'maintenance' }
]);

const statusFilterOptions = computed(() => [
    { label: t('tables.filters.allStatuses'), value: null },
    ...statusOptions.value
]);

const filteredTables = computed(() => {
    let result = tables.value;

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(t => 
            t.name?.toLowerCase().includes(query) ||
            t.location?.toLowerCase().includes(query) ||
            t.description?.toLowerCase().includes(query)
        );
    }

    // Status filter
    if (statusFilter.value) {
        result = result.filter(t => t.status === statusFilter.value);
    }

    return result;
});

const totalTables = computed(() => tables.value.length);
const availableTables = computed(() => tables.value.filter(t => t.status === 'available').length);
const occupiedTables = computed(() => tables.value.filter(t => t.status === 'occupied').length);
const totalCapacity = computed(() => tables.value.reduce((sum, t) => sum + (t.capacity || 0), 0));

const translateStatus = (status) => {
    const statusMap = {
        'available': t('tables.status.available'),
        'occupied': t('tables.status.occupied'),
        'reserved': t('tables.status.reserved'),
        'maintenance': t('tables.status.maintenance')
    };
    return statusMap[status?.toLowerCase()] || status;
};

const getStatusSeverity = (status) => {
    const severityMap = {
        'available': 'success',
        'occupied': 'danger',
        'reserved': 'warning',
        'maintenance': 'secondary'
    };
    return severityMap[status?.toLowerCase()] || 'info';
};

const fetchTables = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/tables');

        if (response.data.success) {
            tables.value = response.data.data;
        }
    } catch (error) {
        console.error('Failed to fetch tables:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('tables.errors.fetchFailed'),
            life: 5000
        });
    } finally {
        loading.value = false;
    }
};

const openCreateDialog = () => {
    dialogMode.value = 'create';
    formData.value = {
        name: '',
        capacity: 4,
        location: '',
        status: 'available',
        description: ''
    };
    showDialog.value = true;
};

const openEditDialog = (table) => {
    dialogMode.value = 'edit';
    selectedTable.value = table;
    formData.value = {
        name: table.name,
        capacity: table.capacity,
        location: table.location || '',
        status: table.status,
        description: table.description || ''
    };
    showDialog.value = true;
};

const saveTable = async () => {
    if (!formData.value.name || !formData.value.capacity) {
        toast.add({
            severity: 'warn',
            summary: t('common.error'),
            detail: t('tables.errors.required'),
            life: 3000
        });
        return;
    }

    saving.value = true;
    try {
        // TODO: Implement create/update API call
        
        if (dialogMode.value === 'create') {
            // Mock create
            const newTable = {
                id: tables.value.length + 1,
                ...formData.value
            };
            tables.value.push(newTable);
            
            toast.add({
                severity: 'success',
                summary: t('common.success'),
                detail: t('tables.messages.created'),
                life: 3000
            });
        } else {
            // Mock update
            const index = tables.value.findIndex(t => t.id === selectedTable.value.id);
            if (index !== -1) {
                tables.value[index] = {
                    ...tables.value[index],
                    ...formData.value
                };
            }
            
            toast.add({
                severity: 'success',
                summary: t('common.success'),
                detail: t('tables.messages.updated'),
                life: 3000
            });
        }
        
        showDialog.value = false;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('tables.errors.saveFailed'),
            life: 5000
        });
    } finally {
        saving.value = false;
    }
};

const confirmDelete = (table) => {
    selectedTable.value = table;
    showDeleteDialog.value = true;
};

const deleteTable = async () => {
    deleting.value = true;
    try {
        // TODO: Implement delete API call
        
        // Mock delete
        const index = tables.value.findIndex(t => t.id === selectedTable.value.id);
        if (index !== -1) {
            tables.value.splice(index, 1);
        }
        
        toast.add({
            severity: 'success',
            summary: t('common.success'),
            detail: t('tables.messages.deleted'),
            life: 3000
        });
        
        showDeleteDialog.value = false;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('tables.errors.deleteFailed'),
            life: 5000
        });
    } finally {
        deleting.value = false;
    }
};

onMounted(() => {
    fetchTables();
});
</script>

<style scoped>
.tables-page {
    padding: 0;
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
    display: flex;
    justify-content: space-between;
    align-items: center;
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
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

.stat-icon.total {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.available {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
}

.stat-icon.occupied {
    background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
}

.stat-icon.capacity {
    background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    line-height: 1;
}

.stat-label {
    color: #718096;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.view-card {
    margin-bottom: 1.5rem;
}

.view-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-controls {
    display: flex;
    gap: 0.75rem;
}

.search-input {
    min-width: 250px;
}

.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.table-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.table-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.table-card.occupied {
    border-left: 4px solid #f56565;
}

.table-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.table-info {
    flex: 1;
}

.table-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0 0 0.5rem 0;
}

.status-tag {
    display: inline-block;
}

.table-actions {
    display: flex;
    gap: 0.25rem;
}

.table-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #4a5568;
    font-size: 0.875rem;
}

.detail-item i {
    color: #718096;
}

.table-description {
    padding-top: 0.75rem;
    border-top: 1px solid #e2e8f0;
    color: #718096;
    font-size: 0.875rem;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.form-field {
    display: flex;
    flex-direction: column;
}

.form-field.full-width {
    grid-column: 1 / -1;
}

.form-field label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #4a5568;
    font-size: 0.875rem;
}
</style>

<template>
    <div class="profile-container">
        <Toast />
        
        <div class="profile-header">
            <h2 class="text-2xl font-bold text-gray-800">{{ $t('profile.title') }}</h2>
            <p class="text-gray-600 mt-1">{{ $t('profile.subtitle') }}</p>
        </div>

        <div class="profile-content">
            <!-- Avatar Section -->
            <Card class="avatar-card">
                <template #content>
                    <div class="avatar-section">
                        <div class="avatar-wrapper">
                            <Avatar 
                                :image="user.avatar || undefined"
                                :label="!user.avatar ? getInitials(user.name) : undefined"
                                size="xlarge"
                                shape="circle"
                                class="avatar-large"
                            />
                            <div class="avatar-actions">
                                <Button 
                                    icon="pi pi-camera" 
                                    rounded 
                                    severity="secondary"
                                    @click="triggerAvatarUpload"
                                    :loading="uploadingAvatar"
                                />
                                <Button 
                                    v-if="user.avatar"
                                    icon="pi pi-trash" 
                                    rounded 
                                    severity="danger"
                                    @click="confirmDeleteAvatar"
                                    :loading="deletingAvatar"
                                />
                            </div>
                        </div>
                        <input 
                            ref="avatarInput" 
                            type="file" 
                            accept="image/*" 
                            @change="handleAvatarUpload"
                            style="display: none"
                        />
                        <div class="avatar-info">
                            <h3 class="text-xl font-semibold">{{ user.name }}</h3>
                            <p class="text-gray-600">{{ user.email }}</p>
                            <Tag :value="user.organizations?.[0]?.name || 'N/A'" severity="info" class="mt-2" />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Profile Information -->
            <Card class="profile-info-card">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span>{{ $t('profile.personalInfo') }}</span>
                        <Button 
                            v-if="!isEditing"
                            :label="$t('common.edit')" 
                            icon="pi pi-pencil" 
                            @click="startEditing"
                            text
                        />
                    </div>
                </template>
                <template #content>
                    <form @submit.prevent="handleUpdateProfile" class="profile-form">
                        <div class="form-grid">
                            <!-- Name Field -->
                            <div class="form-field">
                                <label for="name" class="form-label">
                                    {{ $t('common.name') }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <InputText 
                                    id="name"
                                    v-model="form.name"
                                    :disabled="!isEditing"
                                    :class="{'p-invalid': errors.name}"
                                    class="w-full"
                                />
                                <small v-if="errors.name" class="p-error">{{ errors.name }}</small>
                            </div>

                            <!-- Email Field -->
                            <div class="form-field">
                                <label for="email" class="form-label">
                                    {{ $t('common.email') }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <InputText 
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    :disabled="!isEditing"
                                    :class="{'p-invalid': errors.email}"
                                    class="w-full"
                                />
                                <small v-if="errors.email" class="p-error">{{ errors.email }}</small>
                            </div>

                            <!-- Phone Field -->
                            <div class="form-field">
                                <label for="phone" class="form-label">
                                    {{ $t('common.phone') }}
                                </label>
                                <InputText 
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    :disabled="!isEditing"
                                    :class="{'p-invalid': errors.phone}"
                                    :placeholder="$t('profile.phonePlaceholder')"
                                    class="w-full"
                                />
                                <small v-if="errors.phone" class="p-error">{{ errors.phone }}</small>
                            </div>

                            <!-- Language Field -->
                            <div class="form-field">
                                <label for="language" class="form-label">
                                    {{ $t('common.language') }}
                                </label>
                                <Dropdown 
                                    id="language"
                                    v-model="form.language"
                                    :options="languages"
                                    optionLabel="name"
                                    optionValue="code"
                                    :disabled="!isEditing"
                                    :placeholder="$t('profile.selectLanguage')"
                                    class="w-full"
                                >
                                    <template #value="slotProps">
                                        <div v-if="slotProps.value" class="flex items-center gap-2">
                                            <FlagGeorgia v-if="slotProps.value === 'ka'" :width="20" :height="14" />
                                            <FlagUK v-else-if="slotProps.value === 'en'" :width="20" :height="14" />
                                            <span>{{ getLanguageName(slotProps.value) }}</span>
                                        </div>
                                        <span v-else>{{ slotProps.placeholder }}</span>
                                    </template>
                                    <template #option="slotProps">
                                        <div class="flex items-center gap-2">
                                            <FlagGeorgia v-if="slotProps.option.code === 'ka'" :width="20" :height="14" />
                                            <FlagUK v-else-if="slotProps.option.code === 'en'" :width="20" :height="14" />
                                            <span>{{ slotProps.option.name }}</span>
                                        </div>
                                    </template>
                                </Dropdown>
                                <small v-if="errors.language" class="p-error">{{ errors.language }}</small>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div v-if="isEditing" class="form-actions">
                            <Button 
                                :label="$t('common.cancel')" 
                                severity="secondary" 
                                outlined
                                @click="cancelEditing"
                                type="button"
                            />
                            <Button 
                                :label="$t('common.save')" 
                                icon="pi pi-check"
                                type="submit"
                                :loading="isUpdating"
                            />
                        </div>
                    </form>
                </template>
            </Card>

            <!-- Account Details -->
            <Card class="account-details-card">
                <template #title>
                    {{ $t('profile.accountDetails') }}
                </template>
                <template #content>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">{{ $t('profile.userId') }}</span>
                            <span class="detail-value">#{{ user.id }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">{{ $t('profile.memberSince') }}</span>
                            <span class="detail-value">{{ formatDate(user.created_at) }}</span>
                        </div>
                        <div class="detail-item" v-if="user.organizations && user.organizations.length > 0">
                            <span class="detail-label">{{ $t('profile.organization') }}</span>
                            <span class="detail-value">{{ user.organizations[0].name }}</span>
                        </div>
                        <div class="detail-item" v-if="user.organizations && user.organizations.length > 0">
                            <span class="detail-label">{{ $t('profile.status') }}</span>
                            <Tag 
                                :value="user.organizations[0].status" 
                                :severity="getStatusSeverity(user.organizations[0].status)"
                            />
                        </div>
                        <div class="detail-item" v-if="user.organizations && user.organizations.length > 0">
                            <span class="detail-label">{{ $t('profile.joinedAt') }}</span>
                            <span class="detail-value">{{ formatDate(user.organizations[0].joined_at) }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Security Section -->
            <Card class="security-card">
                <template #title>
                    {{ $t('profile.security') }}
                </template>
                <template #content>
                    <div class="security-actions">
                        <Button 
                            :label="$t('profile.changePassword')" 
                            icon="pi pi-lock"
                            severity="warning"
                            outlined
                            @click="showPasswordDialog = true"
                        />
                    </div>
                </template>
            </Card>
        </div>

        <!-- Change Password Dialog -->
        <Dialog 
            v-model:visible="showPasswordDialog" 
            :header="$t('profile.changePassword')"
            :modal="true"
            :style="{ width: '450px' }"
            :closable="true"
        >
            <form @submit.prevent="handleChangePassword" class="password-form">
                <div class="form-field">
                    <label for="current_password" class="form-label">
                        {{ $t('profile.currentPassword') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <Password 
                        id="current_password"
                        v-model="passwordForm.current_password"
                        :feedback="false"
                        toggleMask
                        :class="{'p-invalid': passwordErrors.current_password}"
                        class="w-full"
                    />
                    <small v-if="passwordErrors.current_password" class="p-error">
                        {{ passwordErrors.current_password }}
                    </small>
                </div>

                <div class="form-field">
                    <label for="new_password" class="form-label">
                        {{ $t('profile.newPassword') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <Password 
                        id="new_password"
                        v-model="passwordForm.new_password"
                        toggleMask
                        :class="{'p-invalid': passwordErrors.new_password}"
                        class="w-full"
                    />
                    <small v-if="passwordErrors.new_password" class="p-error">
                        {{ passwordErrors.new_password }}
                    </small>
                </div>

                <div class="form-field">
                    <label for="new_password_confirmation" class="form-label">
                        {{ $t('profile.confirmPassword') }}
                        <span class="text-red-500">*</span>
                    </label>
                    <Password 
                        id="new_password_confirmation"
                        v-model="passwordForm.new_password_confirmation"
                        :feedback="false"
                        toggleMask
                        :class="{'p-invalid': passwordErrors.new_password_confirmation}"
                        class="w-full"
                    />
                    <small v-if="passwordErrors.new_password_confirmation" class="p-error">
                        {{ passwordErrors.new_password_confirmation }}
                    </small>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <Button 
                        :label="$t('common.cancel')" 
                        severity="secondary" 
                        outlined
                        @click="closePasswordDialog"
                        type="button"
                    />
                    <Button 
                        :label="$t('common.save')" 
                        type="submit"
                        :loading="isChangingPassword"
                    />
                </div>
            </form>
        </Dialog>

        <!-- Delete Avatar Confirmation -->
        <Dialog 
            v-model:visible="showDeleteAvatarDialog" 
            :header="$t('profile.deleteAvatar')"
            :modal="true"
            :style="{ width: '400px' }"
        >
            <p>{{ $t('profile.deleteAvatarConfirm') }}</p>
            <template #footer>
                <Button 
                    :label="$t('common.cancel')" 
                    severity="secondary" 
                    outlined
                    @click="showDeleteAvatarDialog = false"
                />
                <Button 
                    :label="$t('common.delete')" 
                    severity="danger"
                    @click="handleDeleteAvatar"
                    :loading="deletingAvatar"
                />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Avatar from 'primevue/avatar';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import Toast from 'primevue/toast';
import FlagGeorgia from './flags/FlagGeorgia.vue';
import FlagUK from './flags/FlagUK.vue';

const { t } = useI18n();
const toast = useToast();

// User data
const user = ref({
    id: null,
    name: '',
    email: '',
    phone: null,
    avatar: null,
    created_at: null,
    organizations: []
});

// Form state
const isEditing = ref(false);
const isUpdating = ref(false);
const uploadingAvatar = ref(false);
const deletingAvatar = ref(false);
const isChangingPassword = ref(false);
const showPasswordDialog = ref(false);
const showDeleteAvatarDialog = ref(false);

const form = reactive({
    name: '',
    email: '',
    phone: '',
    language: 'ka'
});

const errors = reactive({
    name: '',
    email: '',
    phone: '',
    language: ''
});

const passwordForm = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const passwordErrors = reactive({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const avatarInput = ref(null);

const languages = [
    { code: 'ka', name: 'ქართული' },
    { code: 'en', name: 'English' }
];

// Fetch profile data
const fetchProfile = async () => {
    try {
        const token = localStorage.getItem('partner_token');
        const response = await axios.get('/auth/profile', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.data.success) {
            user.value = response.data.user;
            // Populate form with user data
            form.name = user.value.name;
            form.email = user.value.email;
            form.phone = user.value.phone || '';
            form.language = user.value.language || 'ka';
        }
    } catch (error) {
        console.error('Error fetching profile:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: t('profile.fetchError'),
            life: 3000
        });
    }
};

// Start editing
const startEditing = () => {
    isEditing.value = true;
};

// Cancel editing
const cancelEditing = () => {
    isEditing.value = false;
    // Reset form to original values
    form.name = user.value.name;
    form.email = user.value.email;
    form.phone = user.value.phone || '';
    form.language = user.value.language || 'ka';
    // Clear errors
    Object.keys(errors).forEach(key => errors[key] = '');
};

// Update profile
const handleUpdateProfile = async () => {
    // Clear previous errors
    Object.keys(errors).forEach(key => errors[key] = '');
    
    // Validate
    if (!form.name.trim()) {
        errors.name = t('validation.required');
        return;
    }
    if (!form.email.trim()) {
        errors.email = t('validation.required');
        return;
    }

    isUpdating.value = true;

    try {
        const token = localStorage.getItem('partner_token');
        const response = await axios.put('/auth/profile', form, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (response.data.success) {
            user.value = response.data.user;
            isEditing.value = false;
            
            toast.add({
                severity: 'success',
                summary: t('common.success'),
                detail: t('profile.updateSuccess'),
                life: 3000
            });
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        
        if (error.response?.data?.errors) {
            Object.keys(error.response.data.errors).forEach(key => {
                errors[key] = error.response.data.errors[key][0];
            });
        }
        
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('profile.updateError'),
            life: 3000
        });
    } finally {
        isUpdating.value = false;
    }
};

// Avatar upload
const triggerAvatarUpload = () => {
    avatarInput.value.click();
};

const handleAvatarUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file size (2MB)
    if (file.size > 2048000) {
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: t('profile.avatarTooLarge'),
            life: 3000
        });
        return;
    }

    uploadingAvatar.value = true;

    try {
        const formData = new FormData();
        formData.append('avatar', file);

        const token = localStorage.getItem('partner_token');
        const response = await axios.post('/auth/avatar', formData, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.success) {
            user.value.avatar = response.data.data.avatar_url;
            
            toast.add({
                severity: 'success',
                summary: t('common.success'),
                detail: t('profile.avatarUploadSuccess'),
                life: 3000
            });
        }
    } catch (error) {
        console.error('Error uploading avatar:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('profile.avatarUploadError'),
            life: 3000
        });
    } finally {
        uploadingAvatar.value = false;
        // Reset input
        event.target.value = '';
    }
};

// Delete avatar
const confirmDeleteAvatar = () => {
    showDeleteAvatarDialog.value = true;
};

const handleDeleteAvatar = async () => {
    deletingAvatar.value = true;

    try {
        const token = localStorage.getItem('partner_token');
        const response = await axios.delete('/auth/avatar', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.data.success) {
            user.value.avatar = null;
            showDeleteAvatarDialog.value = false;
            
            toast.add({
                severity: 'success',
                summary: t('common.success'),
                detail: t('profile.avatarDeleteSuccess'),
                life: 3000
            });
        }
    } catch (error) {
        console.error('Error deleting avatar:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('profile.avatarDeleteError'),
            life: 3000
        });
    } finally {
        deletingAvatar.value = false;
    }
};

// Change password
const handleChangePassword = async () => {
    // Clear previous errors
    Object.keys(passwordErrors).forEach(key => passwordErrors[key] = '');
    
    // Validate
    if (!passwordForm.current_password) {
        passwordErrors.current_password = t('validation.required');
        return;
    }
    if (!passwordForm.new_password) {
        passwordErrors.new_password = t('validation.required');
        return;
    }
    if (passwordForm.new_password.length < 8) {
        passwordErrors.new_password = t('validation.minLength', { min: 8 });
        return;
    }
    if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
        passwordErrors.new_password_confirmation = t('validation.passwordMismatch');
        return;
    }

    isChangingPassword.value = true;

    try {
        const token = localStorage.getItem('partner_token');
        const response = await axios.put('/auth/password', passwordForm, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (response.data.success) {
            closePasswordDialog();
            
            toast.add({
                severity: 'success',
                summary: t('common.success'),
                detail: t('profile.passwordChangeSuccess'),
                life: 3000
            });
        }
    } catch (error) {
        console.error('Error changing password:', error);
        
        if (error.response?.data?.errors) {
            Object.keys(error.response.data.errors).forEach(key => {
                passwordErrors[key] = error.response.data.errors[key][0];
            });
        }
        
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || t('profile.passwordChangeError'),
            life: 3000
        });
    } finally {
        isChangingPassword.value = false;
    }
};

const closePasswordDialog = () => {
    showPasswordDialog.value = false;
    passwordForm.current_password = '';
    passwordForm.new_password = '';
    passwordForm.new_password_confirmation = '';
    Object.keys(passwordErrors).forEach(key => passwordErrors[key] = '');
};

// Helper functions
const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const getLanguageName = (code) => {
    const lang = languages.find(l => l.code === code);
    return lang ? lang.name : code;
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('ka-GE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getStatusSeverity = (status) => {
    const severityMap = {
        'active': 'success',
        'inactive': 'warning',
        'suspended': 'danger'
    };
    return severityMap[status] || 'info';
};

// Lifecycle
onMounted(() => {
    fetchProfile();
});
</script>

<style scoped>
.profile-container {
    padding: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.profile-header {
    margin-bottom: 2rem;
}

.profile-content {
    display: grid;
    gap: 1.5rem;
}

/* Avatar Card */
.avatar-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.avatar-card :deep(.p-card-content) {
    padding: 2rem;
}

.avatar-section {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.avatar-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.avatar-large {
    width: 120px !important;
    height: 120px !important;
    font-size: 3rem;
    background: rgba(255, 255, 255, 0.2);
    border: 4px solid white;
}

.avatar-actions {
    display: flex;
    gap: 0.5rem;
}

.avatar-info h3 {
    color: white;
    margin-bottom: 0.5rem;
}

.avatar-info p {
    color: rgba(255, 255, 255, 0.9);
}

/* Profile Info Card */
.profile-info-card {
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

/* Account Details Card */
.account-details-card {
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

.detail-value {
    font-size: 1rem;
    color: #111827;
    font-weight: 600;
}

/* Security Card */
.security-card {
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.security-actions {
    display: flex;
    gap: 1rem;
}

/* Password Form */
.password-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .avatar-section {
        flex-direction: column;
        text-align: center;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }
}

/* Error text */
.p-error {
    color: #ef4444;
    font-size: 0.875rem;
}
</style>

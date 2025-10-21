<template>
    <div class="login-container">
        <Toast />
        
        <div class="login-left">
            <div class="login-branding">
                <div class="logo-container">
                    <img :src="logoUrl" alt="Foodly Logo" class="logo-image">
                </div>
                <h1 class="text-5xl font-bold text-white mb-4">{{ $t('login.brandTitle') }}</h1>
                <p class="text-xl text-white/90 mb-8">{{ $t('login.brandDescription') }}</p>
                <div class="features">
                    <div class="feature-item">
                        <i class="pi pi-calendar"></i>
                        <span>{{ $t('login.features.bookings') }}</span>
                    </div>
                    <div class="feature-item">
                        <i class="pi pi-chart-line"></i>
                        <span>{{ $t('login.features.analytics') }}</span>
                    </div>
                    <div class="feature-item">
                        <i class="pi pi-users"></i>
                        <span>{{ $t('login.features.customers') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-right">
            <div class="login-form-container">
                <div class="form-header">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">{{ $t('login.title') }}</h2>
                    <p class="text-gray-600">{{ $t('login.subtitle') }}</p>
                </div>

                <form @submit.prevent="handleLogin" class="login-form">
                    <!-- Email Field -->
                    <div class="form-field">
                        <label for="email" class="form-label">
                            <i class="pi pi-envelope mr-2"></i>
                            {{ $t('common.email') }}
                        </label>
                        <InputText 
                            id="email" 
                            v-model="form.email" 
                            type="email"
                            :placeholder="$t('login.emailPlaceholder')"
                            class="form-input"
                            :class="{'p-invalid': errors.email}"
                            autocomplete="email"
                        />
                        <small v-if="errors.email" class="error-message">{{ errors.email }}</small>
                    </div>

                    <!-- Password Field -->
                    <div class="form-field">
                        <label for="password" class="form-label">
                            <i class="pi pi-lock mr-2"></i>
                            {{ $t('common.password') }}
                        </label>
                        <Password 
                            id="password" 
                            v-model="form.password" 
                            :placeholder="$t('login.passwordPlaceholder')"
                            class="form-input"
                            :class="{'p-invalid': errors.password}"
                            :feedback="false"
                            toggleMask
                            autocomplete="current-password"
                        />
                        <small v-if="errors.password" class="error-message">{{ errors.password }}</small>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Checkbox v-model="form.remember" inputId="remember" :binary="true" />
                            <label for="remember" class="text-sm text-gray-700 cursor-pointer">{{ $t('common.rememberMe') }}</label>
                        </div>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">{{ $t('common.forgotPassword') }}</a>
                    </div>

                    <!-- Error Message -->
                    <Message v-if="errorMessage" severity="error" :closable="false" class="mt-4">
                        {{ errorMessage }}
                    </Message>

                    <!-- Submit Button -->
                    <Button 
                        type="submit" 
                        :label="$t('common.signIn')"
                        icon="pi pi-sign-in"
                        class="submit-button"
                        :loading="loading"
                        size="large"
                    />
                </form>

                <div class="form-footer">
                    <!-- Language Switcher -->
                    <Dropdown 
                        v-model="currentLocale" 
                        :options="locales" 
                        optionLabel="name" 
                        optionValue="code"
                        @change="changeLocale"
                        class="language-dropdown"
                    >
                        <template #value="slotProps">
                            <div class="flex items-center gap-2">
                                <FlagGeorgia v-if="slotProps.value === 'ka'" :width="20" :height="14" />
                                <FlagUK v-else-if="slotProps.value === 'en'" :width="20" :height="14" />
                                <span class="text-sm">{{ getLocaleName(slotProps.value) }}</span>
                            </div>
                        </template>
                        <template #option="slotProps">
                            <div class="flex items-center gap-2">
                                <FlagGeorgia v-if="slotProps.option.code === 'ka'" :width="20" :height="14" />
                                <FlagUK v-else-if="slotProps.option.code === 'en'" :width="20" :height="14" />
                                <span>{{ slotProps.option.name }}</span>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import FlagGeorgia from './flags/FlagGeorgia.vue';
import FlagUK from './flags/FlagUK.vue';

const toast = useToast();
const { t, locale } = useI18n();
const loading = ref(false);
const errorMessage = ref('');

// Logo URL - using public path
const logoUrl = '/images/logo-h-white.png';

const currentLocale = ref(locale.value);

const locales = [
    { code: 'ka', name: 'ქართული', flag: 'georgia' },
    { code: 'en', name: 'English', flag: 'uk' }
];

const form = reactive({
    email: '',
    password: '',
    remember: false
});

const errors = reactive({
    email: '',
    password: ''
});

const getLocaleName = (code) => {
    return locales.find(l => l.code === code)?.name || '';
};

const changeLocale = (event) => {
    locale.value = event.value;
    localStorage.setItem('locale', event.value);
};

const validateForm = () => {
    let isValid = true;
    errors.email = '';
    errors.password = '';
    errorMessage.value = '';

    if (!form.email) {
        errors.email = t('login.errors.emailRequired');
        isValid = false;
    } else if (!/\S+@\S+\.\S+/.test(form.email)) {
        errors.email = t('login.errors.emailInvalid');
        isValid = false;
    }

    if (!form.password) {
        errors.password = t('login.errors.passwordRequired');
        isValid = false;
    } else if (form.password.length < 6) {
        errors.password = t('login.errors.passwordMinLength');
        isValid = false;
    }

    return isValid;
};

const handleLogin = async () => {
    if (!validateForm()) {
        return;
    }

    loading.value = true;
    errorMessage.value = '';

    try {
        // Get CSRF token first
        await axios.get('/sanctum/csrf-cookie');

        // Make login request
        const response = await axios.post('/auth/login', {
            email: form.email,
            password: form.password,
            remember: form.remember
        });

        console.log('Login Response:', response.data);

        if (response.data.success) {
            toast.add({
                severity: 'success',
                summary: t('common.success'),
                detail: t('login.success.loginSuccess'),
                life: 2000
            });

            // Store user data if needed
            if (response.data.user) {
                localStorage.setItem('user', JSON.stringify(response.data.user));
            }

            // Redirect to dashboard
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1000);
        }
    } catch (error) {
        console.error('Login error:', error);
        console.error('Error response:', error.response?.data);
        console.error('Error status:', error.response?.status);
        
        if (error.response?.status === 422) {
            // Validation errors
            const validationErrors = error.response.data.errors;
            if (validationErrors) {
                errors.email = validationErrors.email?.[0] || '';
                errors.password = validationErrors.password?.[0] || '';
            }
        } else if (error.response?.status === 401) {
            errorMessage.value = t('login.errors.invalidCredentials');
        } else {
            errorMessage.value = error.response?.data?.message || t('login.errors.genericError');
        }
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.login-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 100vh;
}

.login-left {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem;
    position: relative;
    overflow: hidden;
}

.login-left::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    top: -200px;
    left: -200px;
}

.login-left::after {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    bottom: -150px;
    right: -150px;
}

.login-branding {
    position: relative;
    z-index: 1;
    max-width: 500px;
}

.logo-container {
    width: auto;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 2rem;
}

.logo-image {
    height: 100%;
    width: auto;
    object-fit: contain;
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.2));
}

.features {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-top: 3rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: white;
    font-size: 1.1rem;
}

.feature-item i {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.login-right {
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
}

.login-form-container {
    width: 100%;
    max-width: 450px;
    background: white;
    padding: 3rem;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    position: relative;
}

.form-header {
    margin-bottom: 2.5rem;
    text-align: center;
}

.login-form {
    display: flex;
    flex-direction: column;
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
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}

.form-input {
    width: 100%;
}

.form-input :deep(.p-inputtext) {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
}

.form-input :deep(.p-inputtext:focus) {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.error-message {
    color: #ef4444;
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

.submit-button {
    width: 100%;
    margin-top: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 0.85rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.submit-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.submit-button:active {
    transform: translateY(0);
}

.form-footer {
    margin-top: 2rem;
    text-align: center;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: center;
}

.language-dropdown {
    width: 160px;
}

/* Responsive */
@media (max-width: 1024px) {
    .login-container {
        grid-template-columns: 1fr;
    }
    
    .login-left {
        display: none;
    }
    
    .login-right {
        padding: 2rem 1rem;
    }
    
    .login-form-container {
        padding: 2rem;
    }
}

/* PrimeVue Password specific styles */
.form-input :deep(.p-password) {
    width: 100%;
}

.form-input :deep(.p-password input) {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
}

.form-input :deep(.p-password input:focus) {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}
</style>

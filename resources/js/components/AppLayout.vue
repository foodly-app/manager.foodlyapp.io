<template>
    <div class="app-layout">
        <Toast />

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img :src="logoUrl" alt="FOODLY" class="logo" />
                <div class="org-info" v-if="organizationName || restaurantName">
                    <p class="org-name">{{ organizationName }}</p>
                    <p class="restaurant-name">{{ restaurantName }}</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <router-link to="/" class="nav-item" active-class="active">
                    <i class="pi pi-home"></i>
                    <span>{{ $t('menu.dashboard') }}</span>
                </router-link>

                <router-link to="/bookings" class="nav-item" active-class="active">
                    <i class="pi pi-calendar"></i>
                    <span>{{ $t('menu.bookings') }}</span>
                </router-link>

                <router-link to="/calendar" class="nav-item" active-class="active">
                    <i class="pi pi-calendar"></i>
                    <span>{{ $t('menu.calendar') }}</span>
                </router-link>

                <!-- <router-link to="/tables" class="nav-item" active-class="active">
                    <i class="pi pi-table"></i>
                    <span>{{ $t('menu.tables') }}</span>
                </router-link> -->

                <!-- <router-link to="/places" class="nav-item" active-class="active">
                    <i class="pi pi-map-marker"></i>
                    <span>{{ $t('menu.places') }}</span>
                </router-link> -->



                <router-link to="/settings" class="nav-item" active-class="active">
                    <i class="pi pi-cog"></i>
                    <span>{{ $t('menu.settings') }}</span>
                </router-link>
            </nav>

            <div class="sidebar-footer">
                <!-- Language Switcher -->
                <Dropdown v-model="currentLocale" :options="locales" optionLabel="name" optionValue="code"
                    @change="changeLocale" class="language-dropdown">
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

                <Button :label="$t('common.logout')" icon="pi pi-sign-out" severity="danger" text class="w-full"
                    @click="handleLogout" />
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <h1 class="page-title">{{ pageTitle }}</h1>
                </div>
                <div class="header-right">
                    <Button icon="pi pi-bell" severity="secondary" text rounded />
                    <Button icon="pi pi-user" severity="secondary" text rounded @click="router.push('/profile')" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                <router-view v-slot="{ Component }">
                    <transition name="fade" mode="out-in">
                        <component :is="Component" />
                    </transition>
                </router-view>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import FlagGeorgia from './flags/FlagGeorgia.vue';
import FlagUK from './flags/FlagUK.vue';

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { t, locale } = useI18n();

const currentLocale = ref(locale.value);
const organizationName = ref('');
const restaurantName = ref('');

// Logo URL - using public path
const logoUrl = '/images/logo-h-white.png';

const locales = [
    { code: 'ka', name: 'ქართული' },
    { code: 'en', name: 'English' }
];

const pageTitle = computed(() => {
    const titles = {
        'dashboard': t('menu.dashboard'),
        'bookings': t('menu.bookings'),
        'tables': t('menu.tables'),
        'menu': t('menu.menu'),
        'profile': t('common.profile'),
        'settings': t('menu.settings')
    };
    return titles[route.name] || t('menu.dashboard');
});

const getLocaleName = (code) => {
    return locales.find(l => l.code === code)?.name || '';
};

const changeLocale = (event) => {
    locale.value = event.value;
    localStorage.setItem('locale', event.value);
};

const loadUserData = async () => {
    try {
        const response = await axios.get('/auth/initial-dashboard');
        if (response.data.success && response.data.data) {
            const data = response.data.data;
            if (data.organization) {
                organizationName.value = data.organization.name || '';
            }
            if (data.restaurant) {
                restaurantName.value = data.restaurant.name || '';
            }
        }
    } catch (error) {
        console.error('Failed to load user data:', error);
    }
};

const handleLogout = async () => {
    try {
        await axios.post('/auth/logout');

        toast.add({
            severity: 'success',
            summary: t('common.logout'),
            detail: t('login.success.logoutSuccess'),
            life: 2000
        });

        setTimeout(() => {
            window.location.href = '/login';
        }, 1000);
    } catch (error) {
        console.error('Logout error:', error);
        toast.add({
            severity: 'error',
            summary: t('common.error'),
            detail: error.response?.data?.message || 'Logout failed',
            life: 3000
        });
    }
};

onMounted(() => {
    loadUserData();
});
</script>

<style scoped>
.app-layout {
    display: flex;
    height: 100vh;
    overflow: hidden;
}

.sidebar {
    width: 280px;
    background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    flex-direction: column;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo {
    height: 40px;
    margin-bottom: 1rem;
}

.org-info {
    margin-top: 1rem;
}

.org-name {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.restaurant-name {
    font-size: 0.875rem;
    opacity: 0.8;
}

.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
    overflow-y: auto;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.875rem 1.5rem;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.2s;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.nav-item.active {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-left: 4px solid white;
}

.nav-item i {
    font-size: 1.25rem;
}

.sidebar-footer {
    padding: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.language-dropdown {
    width: 100%;
    margin-bottom: 1rem;
}

.main-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #f5f7fa;
}

.top-header {
    background: white;
    padding: 1.5rem 2rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin: 0;
}

.header-right {
    display: flex;
    gap: 0.5rem;
}

.page-content {
    flex: 1;
    overflow-y: auto;
    padding: 2rem;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

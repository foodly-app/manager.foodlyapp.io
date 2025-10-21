import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../components/Dashboard.vue';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Dashboard', requiresAuth: true }
    },
    {
        path: '/bookings',
        name: 'bookings',
        component: () => import('../components/Bookings.vue'),
        meta: { title: 'Bookings', requiresAuth: true }
    },
    {
        path: '/tables',
        name: 'tables',
        component: () => import('../components/Tables.vue'),
        meta: { title: 'Tables', requiresAuth: true }
    },
    {
        path: '/menu',
        name: 'menu',
        component: () => import('../components/Menu.vue'),
        meta: { title: 'Menu', requiresAuth: true }
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => import('../components/Profile.vue'),
        meta: { title: 'Profile', requiresAuth: true }
    },
    {
        path: '/settings',
        name: 'settings',
        component: () => import('../components/Settings.vue'),
        meta: { title: 'Settings', requiresAuth: true }
    },
    // 404 fallback - redirect to dashboard
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guard to update page title
router.beforeEach((to, from, next) => {
    document.title = to.meta.title ? `${to.meta.title} - FOODLY` : 'FOODLY Partner Panel';
    next();
});

export default router;

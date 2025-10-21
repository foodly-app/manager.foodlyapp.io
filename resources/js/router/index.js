import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../components/Dashboard.vue';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Dashboard' }
    },
    {
        path: '/bookings',
        name: 'bookings',
        component: () => import('../components/Bookings.vue'),
        meta: { title: 'Bookings' }
    },
    {
        path: '/tables',
        name: 'tables',
        component: () => import('../components/Tables.vue'),
        meta: { title: 'Tables' }
    },
    {
        path: '/menu',
        name: 'menu',
        component: () => import('../components/Menu.vue'),
        meta: { title: 'Menu' }
    },
    {
        path: '/settings',
        name: 'settings',
        component: () => import('../components/Settings.vue'),
        meta: { title: 'Settings' }
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

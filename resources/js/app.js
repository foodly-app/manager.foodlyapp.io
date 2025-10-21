import './bootstrap';
import { createApp } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import i18n from './i18n';
import router from './router';

// Import PrimeVue components
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import Dialog from 'primevue/dialog';
import Menu from 'primevue/menu';
import Chart from 'primevue/chart';
import Message from 'primevue/message';
import Dropdown from 'primevue/dropdown';
import ProgressSpinner from 'primevue/progressspinner';

// Import components
import AppLayout from './components/AppLayout.vue';
import Login from './components/Login.vue';

// Determine which component to mount based on current page
const appElement = document.getElementById('app');

if (appElement) {
    const page = appElement.dataset.page || 'app';
    let rootComponent;

    // Select component based on page
    if (page === 'login') {
        rootComponent = Login;
    } else if (page === 'app') {
        rootComponent = AppLayout;
    }

    if (rootComponent) {
        const app = createApp(rootComponent);

        // Configure PrimeVue
        app.use(PrimeVue, {
            theme: {
                preset: Aura,
                options: {
                    darkModeSelector: '.dark-mode',
                }
            }
        });

        app.use(ToastService);
        app.use(i18n);
        
        // Use router only for app layout
        if (page === 'app') {
            app.use(router);
        }

        // Register PrimeVue components globally
        app.component('Button', Button);
        app.component('Card', Card);
        app.component('DataTable', DataTable);
        app.component('Column', Column);
        app.component('InputText', InputText);
        app.component('Password', Password);
        app.component('Checkbox', Checkbox);
        app.component('Toast', Toast);
        app.component('Dialog', Dialog);
        app.component('Menu', Menu);
        app.component('Chart', Chart);
        app.component('Message', Message);
        app.component('Dropdown', Dropdown);
        app.component('ProgressSpinner', ProgressSpinner);

        // Mount app
        app.mount('#app');
    }
}

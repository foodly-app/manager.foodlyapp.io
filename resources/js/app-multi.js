import './bootstrap';
import { createApp } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import ToastService from 'primevue/toastservice';

// Import PrimeVue components
import Button from 'primevue/button';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Toast from 'primevue/toast';
import Dialog from 'primevue/dialog';
import Menu from 'primevue/menu';
import Chart from 'primevue/chart';
import Message from 'primevue/message';

// Function to create and configure app
function createVueApp(component) {
    const app = createApp(component);

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

    return app;
}

// Mount appropriate component based on page
const appElement = document.getElementById('app');
if (appElement) {
    const page = appElement.dataset.page;
    
    let component;
    
    if (page === 'login') {
        import('./components/Login.vue').then(module => {
            component = module.default;
            const app = createVueApp(component);
            app.mount('#app');
        });
    } else if (page === 'dashboard') {
        import('./components/Dashboard.vue').then(module => {
            component = module.default;
            const app = createVueApp(component);
            app.mount('#app');
        });
    }
}

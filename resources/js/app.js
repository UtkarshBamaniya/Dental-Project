import '../css/app.css';
import './bootstrap';
import './assets/styles.scss';
import 'primeflex/primeflex.css';

import Aura from '@primeuix/themes/aura';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import PrimeVue from 'primevue/config';
import Button from 'primevue/button';
import Chart from 'primevue/chart';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: '.app-dark',
                    },
                },
            })
            .use(ZiggyVue)
            .component('Button', Button)
            .component('Chart', Chart)
            .component('Column', Column)
            .component('DataTable', DataTable)
            .component('Tag', Tag)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

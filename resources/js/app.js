import './bootstrap';
import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp, Link } from '@inertiajs/vue3'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import ReportCharts from './Components/ReportsChart.vue';
import PrimeVue from 'primevue/config';
import Dialog from 'primevue/dialog';
createInertiaApp({
  title:() => 'PPD Briefer',
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .component('Link', Link)
      .component('Dialog', Dialog)
      .use(PrimeVue)
      .use(ReportCharts)
      .mount(el)
  },
})
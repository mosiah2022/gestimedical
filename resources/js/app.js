require('./bootstrap');

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import PrimeVue from 'primevue/config';
import mitt from "mitt";
import VueSweetalert2 from "vue-sweetalert2";

import 'primevue/resources/themes/saga-blue/theme.css'
import 'primevue/resources/primevue.min.css'
import 'primeicons/primeicons.css'
import 'primeflex/primeflex.css'

//import libraries
import AutoComplete from 'primevue/autocomplete';
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import ToastService from 'primevue/toastservice';
import Toast from "primevue/toast";
import Card from "primevue/card";
import Textarea from "primevue/textarea";
import Button from "primevue/button";
import Dropdown from "primevue/dropdown";
import Calendar from "primevue/calendar";
import RadioButton from 'primevue/radiobutton';
import InputMask from 'primevue/inputmask';
import Checkbox from 'primevue/checkbox';
import Slider from "primevue/slider";
import ProgressSpinner from 'primevue/progressspinner';
import FileUpload from 'primevue/fileupload';
import Message from 'primevue/message';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Asispharma';
const emitter = mitt();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => require(`./Pages/${name}.vue`),
    setup({ el, app, props, plugin }) {
        const vueApp = createApp({ render: () => h(app, props) });
        vueApp
            .component('AutoComplete', AutoComplete)
            .component('DataTable', DataTable)
            .component('InputText', InputText)
            .component('InputNumber', InputNumber)
            .component('Column', Column)
            .component('Dialog', Dialog)
            .component('Toast', Toast)
            .component('Card', Card)
            .component('TextArea', Textarea)
            .component('PrimeButton', Button)
            .component('Dropdown', Dropdown)
            .component('Calendar', Calendar)
            .component('RadioButton', RadioButton)
            .component('InputMask', InputMask)
            .component('Checkbox', Checkbox)
            .component('Slider', Slider)
            .component('ProgressSpinner', ProgressSpinner)
            .component('FileUpload', FileUpload)
            .component('Message', Message)
            .component('TabView', TabView)
            .component('TabPanel', TabPanel)
            .use(plugin)
            .use(PrimeVue)
            .use(ToastService)
            .use(VueSweetalert2)
            .mixin({ methods: { route } })

        vueApp.config.globalProperties.emitter = emitter;
        vueApp.config.globalProperties.$route = route

        vueApp.mount(el)


        return vueApp
    },
});

InertiaProgress.init({ color: '#4B5563' });

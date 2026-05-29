import './bootstrap';
import 'toastr/build/toastr.min.css';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import Routes from './routes';
import Login from './pages/auth/Login.vue';
import VueHtmlToPaper from 'vue-html-to-paper';
import PrimeVue from "primevue/config";
import Aura from '@primevue/themes/aura';
import VueSignaturePad from 'vue-signature-pad';
import Vue3Signature from "vue3-signature"
import i18n from './i18n'; // Importando o i18n



const options = {
    name: '_blank',
    specs: [
      'fullscreen=yes',
      'titlebar=yes',
      'scrollbars=yes'
    ],
    styles: [
      'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
      'https://unpkg.com/kidlat-css/css/kidlat.css'
    ],
    timeout: 1000, // default timeout before the print window appears
    autoClose: true, // if false, the window will not close after printing
    windowTitle: window.document.title, // override the window title
  }

const app = createApp({});
const router = createRouter({
    routes:Routes,
    history:createWebHistory(),
});


app.use(i18n)
app.use(Vue3Signature);
app.use(PrimeVue, {
  unstyled: true
});

app.component('Login',Login);
app.use(router,VueHtmlToPaper, options);
app.mount('#app');
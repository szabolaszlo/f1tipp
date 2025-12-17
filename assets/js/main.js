import { createApp } from 'vue';
import App from './components/App.vue';
import Router from './router/index';
import { createI18n } from 'vue-i18n';
import messages from './translations';

// Create i18n instance
const i18n = createI18n({
    locale: 'hu',
    fallbackLocale: 'en',
    messages,
});

// Create single Vue app instance
const app = createApp(App);

// Use plugins
app.use(Router);
app.use(i18n);

// Mount the app
app.mount('#app');

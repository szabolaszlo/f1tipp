import {createApp} from 'vue';
import MainComponent from './components/MainComponent';
import Navigation from './components/Navigation';
import Router from './router/index';
import {createI18n} from 'vue-i18n'
import messages from './translations'

window.onload = function () {
    const i18n = createI18n({
        locale: 'hu',
        fallbackLocale: 'en',
        messages
    })

    const navigation = createApp(Navigation);
    navigation.use(Router);
    navigation.use(i18n);
    navigation.mount('#navigation');

    const mainComponent = createApp(MainComponent);
    mainComponent.use(Router);
    mainComponent.use(i18n);
    mainComponent.mount('#app')
}

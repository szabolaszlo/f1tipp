import {createApp} from 'vue';
import MainComponent from './components/MainComponent';
import Navigation from './components/Navigation';
import News from './components/News';
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

    const newsComponent = createApp(News);
    newsComponent.use(Router);
    newsComponent.use(i18n);
    newsComponent.mount('#news')

    const newsComponentOnMobile = createApp(News);
    newsComponentOnMobile.use(Router);
    newsComponentOnMobile.use(i18n);
    newsComponentOnMobile.mount('#news-on-mobile')
}

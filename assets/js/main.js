import {createApp} from 'vue';
import MainComponent from './components/MainComponent';
import Navigation from './components/Navigation';
import Router from './router/index';

window.onload = function () {
    const navigation = createApp(Navigation);
    navigation.use(Router);
    navigation.mount('#navigation');

    const mainComponent = createApp(MainComponent);
    mainComponent.use(Router);
    mainComponent.mount('#app')
}
import Vue from 'vue';
import MainComponent from './components/MainComponent';
import Navigation from './components/Navigation';
import Router from './router/index';
//import Skeleton from 'vue-loading-skeleton';
//import "vue-loading-skeleton/dist/style.css"

//Vue.use(Skeleton);

window.onload = function () {
    new Vue({
        el: '#navigation',
        router: Router,
        render: h => h(Navigation)
    });

    new Vue({
        el: '#app',
        router: Router,
        render: h => h(MainComponent)
    });
}
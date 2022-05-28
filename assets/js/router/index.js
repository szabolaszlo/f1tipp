import {createRouter} from 'vue-router'
import {createWebHashHistory} from 'vue-router'
import {createApp} from 'vue'

const routes = [
    {
        path: '/calendar',
        name: 'Calendar',
        component: () => import(/* webpackChunkName: "about" */ '../components/Calendar.vue')
    },
    {
        path: '/rules',
        name: 'Rules',
        component: () => import(/* webpackChunkName: "about" */ '../components/Rules.vue')
    },
    {
        path: '/actual',
        name: 'Actual',
        component: () => import(/* webpackChunkName: "about" */ '../components/Actual.vue')
    },
    {
        path: '/',
        component: () => import(/* webpackChunkName: "about" */ '../components/Actual.vue')
    },
    {
        path: '/home',
        name: 'Home',
        component: () => import(/* webpackChunkName: "about" */ '../components/MainComponent.vue'),
        meta: {transition: 'slide-left'},
    }
]

const router = createRouter({
    // 4. Provide the history implementation to use. We are using the hash history for simplicity here.
    history: createWebHashHistory(),
    routes, // short for `routes: routes`
})

// 5. Create and mount the root instance.
const app = createApp()
// Make sure to _use_ the router instance to make the
// whole app router-aware.
app.use(router)

export default router

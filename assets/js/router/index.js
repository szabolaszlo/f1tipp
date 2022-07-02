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
        path: '/results',
        name: 'Results',
        component: () => import(/* webpackChunkName: "about" */ '../components/Results.vue')
    },
    {
        path: '/rules',
        name: 'Rules',
        component: () => import(/* webpackChunkName: "about" */ '../components/Rules.vue')
    },
    {
        path: '/statistics',
        name: 'Statistics',
        component: () => import(/* webpackChunkName: "about" */ '../components/Statistics.vue')
    },
    {
        path: '/actual',
        name: 'Actual',
        component: () => import(/* webpackChunkName: "about" */ '../components/Actual.vue')
    },
    {
        path: "/",
        redirect: "/actual",
    }
]

const router = createRouter({
    // 4. Provide the history implementation to use. We are using the hash history for simplicity here.
    history: createWebHashHistory(),
    routes, // short for `routes: routes`
    scrollBehavior(to, from, savedPosition) {
        return {top: 0, behavior: 'smooth'}
    }
})

// 5. Create and mount the root instance.
const app = createApp()
// Make sure to _use_ the router instance to make the
// whole app router-aware.
app.use(router)

export default router

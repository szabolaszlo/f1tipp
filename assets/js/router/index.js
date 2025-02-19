import {createRouter} from 'vue-router'
import {createWebHashHistory} from 'vue-router'
import {createApp} from 'vue'

const routes = [
    {
        path: '/calendar',
        name: 'Calendar',
        component: () => import(/* webpackChunkName: "calendar" */ '../components/Calendar.vue')
    },
    {
        path: '/betting',
        name: 'Betting',
        component: () => import(/* webpackChunkName: "betting" */ '../components/Betting.vue')
    },
    {
        path: '/results',
        name: 'Results',
        component: () => import(/* webpackChunkName: "results" */ '../components/Results.vue')
    },
    {
        path: '/rules',
        name: 'Rules',
        component: () => import(/* webpackChunkName: "rules" */ '../components/Rules.vue')
    },
    {
        path: '/statistics',
        name: 'Statistics',
        component: () => import(/* webpackChunkName: "statistics" */ '../components/Statistics.vue')
    },
    {
        path: '/actual',
        name: 'Actual',
        component: () => import(/* webpackChunkName: "actual" */ '../components/Actual.vue')
    },
    {
        path: '/trophies',
        name: 'Trophies',
        component: () => import(/* webpackChunkName: "trophies" */ '../components/Trophies.vue')
    },
    {
        path: '/login',
        name: 'Login',
        component: () => import(/* webpackChunkName: "trophies" */ '../components/Login.vue')
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

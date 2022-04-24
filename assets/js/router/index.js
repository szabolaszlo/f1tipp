import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const routes = [
    {
        path: '/app-one',
        name: 'App',
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        component: () => import(/* webpackChunkName: "about" */ '../components/App'),
        meta: { transition: 'slide-left' },
    },
    {
        path: '/app-two',
        name: 'App2',
        component: () => import(/* webpackChunkName: "about" */ '../components/App2.vue'),
        meta: { transition: 'slide-left' },
    },
    {
        path: '/home',
        name: 'Home',
        component: () => import(/* webpackChunkName: "about" */ '../components/MainComponent.vue'),
        meta: { transition: 'slide-left' },
    }
]

const router = new VueRouter({
    routes
})

export default router

import { createRouter } from 'vue-router'
import { createWebHashHistory } from 'vue-router'
import { createApp } from 'vue'

const routes = [
    {
        path: '/app-one',
        name: 'App',
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        component: () => import(/* webpackChunkName: "about" */ '../components/App'),
        meta: {transition: 'slide-left'},
    },
    {
        path: '/app-two',
        name: 'App2',
        component: () => import(/* webpackChunkName: "about" */ '../components/App2.vue'),
        meta: {transition: 'slide-left'},
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

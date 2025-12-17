import { createRouter, createWebHistory } from 'vue-router';

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
        component: () => import(/* webpackChunkName: "login" */ '../components/Login.vue')
    },
    {
        path: '/',
        redirect: '/actual',
    },
    {
        // Catch-all route for SPA - redirect unknown routes to actual
        path: '/:pathMatch(.*)*',
        redirect: '/actual',
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return { top: 0, behavior: 'smooth' };
    }
});

export default router;


import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/betting',
        name: 'Betting',
        component: () => import(/* webpackChunkName: "betting" */ '../components/Betting.vue')
    },
    {
        path: '/actual',
        name: 'Actual',
        component: () => import(/* webpackChunkName: "actual" */ '../components/Actual.vue')
    },
    {
        path: '/standings',
        name: 'Standings',
        component: () => import(/* webpackChunkName: "standings" */ '../components/Standings.vue')
    },
    {
        path: '/calendar',
        name: 'Calendar',
        component: () => import(/* webpackChunkName: "calendar" */ '../components/Calendar.vue')
    },
    {
        path: '/profile',
        name: 'Profile',
        component: () => import(/* webpackChunkName: "profile" */ '../components/Profile.vue')
    },
    {
        path: '/login',
        name: 'Login',
        redirect: '/profile'
    },
    {
        path: '/results',
        name: 'Results',
        redirect: '/standings'
    },
    {
        path: '/trophies',
        name: 'Trophies',
        redirect: '/standings'
    },
    {
        path: '/statistics',
        name: 'Statistics',
        component: () => import(/* webpackChunkName: "statistics" */ '../components/Statistics.vue')
    },
    {
        path: '/rules',
        name: 'Rules',
        component: () => import(/* webpackChunkName: "rules" */ '../components/Rules.vue')
    },
    {
        path: '/',
        redirect: '/actual',
    },
    {
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


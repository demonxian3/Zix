import Home from '../pages/Home.vue';

export default {
    mode: 'hash',
    routes: [
        {
          path: '/',
          redirect: '/home',
        },
        {
            name: 'home',
            path: '/home',
            component: Home,
            meta: {  keepAlive: false, canback: false},

        },
    ]
}

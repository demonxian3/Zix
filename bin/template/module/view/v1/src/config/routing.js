import Home from '../pages/Home.vue'
import Common from '../pages/Common.vue'
//TOCHANGE
export default {
    mode: 'hash',
    routes: [{
            path: '/home',
            redirect: '/',
        }, {
            path: '/',
            component: Home,
        },
        {
            path: '/common',
            component: Common,
        },
    ]
}
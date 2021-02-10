import Vue from 'vue'
import App from './App.vue'
import VueRouter from 'vue-router'

/** config */
import api from '@/config/api.js'
import setting from '@/config/setting'
import routing from '@/config/routing'
import constant from '@/config/constant'
import Kpman from '@/lib/kpman'
import Toast from '@/lib/toast'


/** components */
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue'

/** class */
import 'font-awesome/css/font-awesome.min.css'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-vue/dist/bootstrap-vue.min.css'

/** init  */
import './lib/index.js'

import Utils from './lib/utils.js'

Vue.prototype.$utils = Utils;
Vue.prototype.$setting  = setting;

/** register */
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(VueRouter);
Vue.use(Toast);
const router = new VueRouter(routing);
Vue.use(Kpman, {setting, router, api});

Object.assign(Vue.prototype, constant);
Object.assign(global, constant);

new Vue({
    el: '#app',
    render: h => h(App),
    router,
});

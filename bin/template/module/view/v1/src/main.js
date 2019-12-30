import Vue from 'vue'
import App from './App.vue'

/** config */
import Configs from './config/config.js'
import Routings from './config/routing.js'

/** components */
import Http from './assets/js/http.js'
import Utils from './assets/js/utils.js'
import BootstrapVue from 'bootstrap-vue'
import VueRouter from 'vue-router'

//TOCHANGE
import Zix from './components/zix';

/** class */
import 'font-awesome/css/font-awesome.min.css'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-vue/dist/bootstrap-vue.min.css'

/** init  */
import './assets/js/index.js'

/** register */
//TOCHANGE
Vue.use(Zix);
Vue.use(BootstrapVue);
Vue.use(VueRouter);
Vue.prototype.$http = Http;
Vue.prototype.$utils = Utils;
Vue.prototype.$config = Configs;

Http.setHost(Configs.host);
const router = new VueRouter(Routings);

new Vue({
    el: '#app',
    render: h => h(App),
    router,
})
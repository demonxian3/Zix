import Topnav from './Topnav.vue'
import Bubbles from './Bubbles.vue'
import Collapse from './Collapse.vue'
import Fgroup from './Fgroup.vue'

export default {
    install(Vue) {
        Vue.component('zix-topnav', Topnav);
        Vue.component('zix-bubbles', Bubbles);
        Vue.component('zix-collapse', Collapse);
        Vue.component('zix-fgroup', Fgroup);
    }
}
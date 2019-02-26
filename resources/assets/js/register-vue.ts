import BootstrapVue from 'bootstrap-vue';
import Vue from 'vue';
import Peiling from './components/peilingen/Peiling.vue';
import PeilingOptie from './components/peilingen/PeilingOptie.vue';
Vue.component('peiling', Peiling);
Vue.component('peilingoptie', PeilingOptie);
Vue.use(BootstrapVue);

import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue';
import Peiling from './components/peilingen/Peiling';
import PeilingOptie from './components/peilingen/PeilingOptie';
Vue.component('peiling', Peiling);
Vue.component('peilingoptie', PeilingOptie);
Vue.use(BootstrapVue)

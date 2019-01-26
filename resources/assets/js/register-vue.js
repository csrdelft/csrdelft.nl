import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue';
import Peiling from './components/peilingen/Peiling';
import PeilingOptie from './components/peilingen/PeilingOptie';
import KetzerTovenaar from './components/ketzertovenaar/KetzerTovenaar';

Vue.component('peiling', Peiling);
Vue.component('peilingoptie', PeilingOptie);
Vue.component('ketzertovenaar', KetzerTovenaar);
Vue.use(BootstrapVue);


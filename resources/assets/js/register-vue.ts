import BootstrapVue from 'bootstrap-vue';
import Vue from 'vue';
import ToggleButton from 'vue-js-toggle-button';

import KetzerTovenaar from './components/ketzertovenaar/KetzerTovenaar';
import Peiling from './components/peilingen/Peiling.vue';
import PeilingOptie from './components/peilingen/PeilingOptie.vue';

Vue.component('peiling', Peiling);
Vue.component('peilingoptie', PeilingOptie);
Vue.component('ketzertovenaar', KetzerTovenaar);
Vue.use(ToggleButton);
Vue.use(BootstrapVue);

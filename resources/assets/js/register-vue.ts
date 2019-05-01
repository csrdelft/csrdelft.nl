import BootstrapVue from 'bootstrap-vue';
import Vue from 'vue';
import Groep from './components/groep/Groep.vue';
import Peiling from './components/peilingen/Peiling.vue';
import PeilingOptie from './components/peilingen/PeilingOptie.vue';
Vue.component('peiling', Peiling);
Vue.component('peilingoptie', PeilingOptie);
Vue.component('groep', Groep);
Vue.use(BootstrapVue);

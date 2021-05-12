import BootstrapVue from 'bootstrap-vue';
import Vue from 'vue';
import DeclaratieVue from './components/declaratie/Declaratie.vue';
import Groep from './components/groep/Groep.vue';
import NamenLeren from './components/namen-leren/NamenLeren.vue';
import Peiling from './components/peilingen/Peiling.vue';
import PeilingOptie from './components/peilingen/PeilingOptie.vue';
import GroepPrompt from './components/editor/GroepPrompt.vue';
import VueInputMask from 'vue-inputmask';
// const VueInputMask = require('vue-inputmask').default

Vue.component('peiling', Peiling);
Vue.component('peilingoptie', PeilingOptie);
Vue.component('groep', Groep);
Vue.component('namenleren', NamenLeren);
Vue.component('declaratie', DeclaratieVue);
Vue.component('groepprompt', GroepPrompt);

Vue.use(VueInputMask)
Vue.use(BootstrapVue);

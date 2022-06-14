import BootstrapVue from 'bootstrap-vue';
import Vue from 'vue';
import Icon from './components/common/Icon.vue';
import Declaratie from './components/declaratie/Declaratie.vue';
import Groep from './components/groep/Groep.vue';
import NamenLeren from './components/namen-leren/NamenLeren.vue';
import Peiling from './components/peilingen/Peiling.vue';
import PeilingOptie from './components/peilingen/PeilingOptie.vue';
import GroepPrompt from './components/editor/GroepPrompt.vue';
import Inputmask from 'inputmask';
import money from 'v-money';

Vue.component('icon', Icon);
Vue.component('peiling', Peiling);
Vue.component('peilingoptie', PeilingOptie);
Vue.component('groep', Groep);
Vue.component('namenleren', NamenLeren);
Vue.component('declaratie', Declaratie);
Vue.component('groepprompt', GroepPrompt);

Vue.directive('input-mask', {
	bind: function (el) {
		new Inputmask().mask(el);
	},
});
Vue.use(money, { precision: 2, decimal: ',', thousands: ' ', prefix: '€ ' });
Vue.use(BootstrapVue);

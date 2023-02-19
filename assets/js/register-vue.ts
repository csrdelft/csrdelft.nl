import BootstrapVue from 'bootstrap-vue';
import { createApp } from 'vue';
import type { Component } from 'vue';
import Icon from './components/common/Icon.vue';
import Declaratie from './components/declaratie/Declaratie.vue';
import Groep from './components/groep/Groep.vue';
import NamenLeren from './components/namen-leren/NamenLeren.vue';
import Peiling from './components/peilingen/Peiling.vue';
import PeilingOptie from './components/peilingen/PeilingOptie.vue';
import GroepPrompt from './components/editor/GroepPrompt.vue';
import Inputmask from 'inputmask';
import money from 'v-money';

export const createDefaultApp = (rootComponent: Component) => {
	const app = createApp(rootComponent);

	// Via @vue/compat
	app.use(BootstrapVue);
	app.directive('input-mask', {
		beforeMount: function (el) {
			new Inputmask().mask(el);
		},
	});
	app.component('icon', Icon);
	app.component('peiling', Peiling);
	app.component('peilingoptie', PeilingOptie);
	app.component('groep', Groep);
	app.component('namenleren', NamenLeren);
	app.component('declaratie', Declaratie);
	app.component('groepprompt', GroepPrompt);

	app.use(money, { precision: 2, decimal: ',', thousands: ' ', prefix: '€ ' });

	return app;
};

import type { App, Component } from 'vue';
import { createApp } from 'vue';
import Icon from './components/common/Icon.vue';
import Declaratie from './components/declaratie/Declaratie.vue';
import Groep from './components/groep/Groep.vue';
import NamenLeren from './components/namen-leren/NamenLeren.vue';
import Peiling from './components/peilingen/Peiling.vue';
import PeilingOptie from './components/peilingen/PeilingOptie.vue';
import GroepPrompt from './components/editor/GroepPrompt.vue';
import Inputmask from 'inputmask';
import money from 'v-money';

// Map naam naar vue component
const vueMap = {
	icon: Icon,
	peiling: Peiling,
	peilingoptie: PeilingOptie,
	groep: Groep,
	namenleren: NamenLeren,
	declaratie: Declaratie,
	groepprompt: GroepPrompt,
};

export const getVueComponent = (naam: string): Component => vueMap[naam];

export const createDefaultApp = (
	rootComponent: Component,
	rootProps?: Record<string, unknown>
): App<Element> => {
	const app = createApp(rootComponent, rootProps);

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

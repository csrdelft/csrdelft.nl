import type { App, Component } from 'vue';
import { createApp } from 'vue';
import Declaratie from './components/declaratie/Declaratie.vue';
import Groep from './components/groep/Groep.vue';
import NamenLeren from './components/namen-leren/NamenLeren.vue';
import Peiling from './components/peilingen/Peiling.vue';
import Inputmask from 'inputmask';
import money from 'v-money';

// Map naam naar vue component
const vueMap = {
	peiling: Peiling,
	groep: Groep,
	namenleren: NamenLeren,
	declaratie: Declaratie,
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
	app.use(money, { precision: 2, decimal: ',', thousands: ' ', prefix: '€ ' });

	return app;
};

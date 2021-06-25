import BootstrapVue from 'bootstrap-vue';
import Vue from 'vue';
import ToggleButton from 'vue-js-toggle-button';

import VCalendar from 'v-calendar';
import 'v-calendar/lib/v-calendar.min.css';

import Groep from './components/groep/Groep.vue';
import KetzerTovenaar from './components/ketzertovenaar/KetzerTovenaar';
import NamenLeren from './components/namen-leren/NamenLeren.vue';
import Peiling from './components/peilingen/Peiling.vue';
import PeilingOptie from './components/peilingen/PeilingOptie.vue';
import GroepPrompt from './components/editor/GroepPrompt.vue';

Vue.component('peiling', Peiling);
Vue.component('peilingoptie', PeilingOptie);
Vue.component('ketzertovenaar', KetzerTovenaar);
Vue.component('groep', Groep);
Vue.component('namenleren', NamenLeren);
Vue.component('groepprompt', GroepPrompt);
Vue.use(ToggleButton);

Vue.use(BootstrapVue);
Vue.use(VCalendar, {
	firstDayOfWeek: 1,
	locale: 'nl-NL',
	masks: {
		L: 'DD-MM-YYYY',
		weekdays: 'WW',
		dayPopover: 'WWW, D MMM YYYY',
		input: ['L', 'DD-MM-YYYY', 'DD-MM-YYYY'],
		data: ['L', 'DD-MM-YYYY', 'DD-MM-YYYY'],
	},
	locales: {
		'nl-NL': {
			masks: {
				L: 'DD-MM-YYYY',
				weekdays: 'WW',
				dayPopover: 'WWW, D MMM YYYY',
				input: ['L', 'DD-MM-YYYY', 'DD-MM-YYYY'],
				data: ['L', 'DD-MM-YYYY', 'DD-MM-YYYY'],
			},
			dayNames: ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'],
			dayNamesShort: ['zon', 'maa', 'din', 'woe', 'don', 'vri', 'zat'],
			dayNamesShorter: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
			dayNamesNarrow: ['z', 'm', 'd', 'w', 'd', 'v', 'z'],
			monthNames: ['januari', 'februari', 'maart', 'april', 'mei', 'juni',
				'juli', 'augustus', 'september', 'oktober', 'november', 'december'],
			monthNamesShort: ['jan', 'feb', 'maa', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'],
		},
	},
});

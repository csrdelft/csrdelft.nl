/**
 * Laad alle externe libs en knoop de goede dingen aan elkaar.
 */
import Bloodhound from 'corejs-typeahead';
import Dropzone from 'dropzone';
import $ from 'jquery';
import moment from 'moment';
import {
	registerBbContext,
	registerDataTableContext,
	registerFormulierContext,
	registerGlobalContext,
	registerGrafiekContext,
	registerKnopContext,
} from './context';
import {init} from './ctx';
import {ketzerAjax} from './lib/ajax';
import {importAgenda} from './lib/courant';
import {initSluitMeldingen} from './lib/csrdelft';
import {domUpdate} from './lib/domUpdate';
import {formCancel, formInlineToggle, formSubmit, insertPlaatje} from './lib/formulier';
import {forumBewerken, saveConceptForumBericht} from './lib/forum';
import {takenColorSuggesties, takenShowOld, takenToggleDatum, takenToggleSuggestie} from './lib/maalcie';
import {docReady} from './lib/util';

moment.locale('nl');

declare global {
	interface JQueryStatic {
		timeago: any;
		markItUp: (arg: any) => any;
	}

	interface JQuery {
		timeago: () => void;
		markItUp: (arg: any) => any;
		hoverIntent: (arg: any, arg1?: any) => any;
		autosize: () => void;
		scrollTo: (arg: any) => void;
		modal: (arg?: any) => void;
	}
}

window.$ = window.jQuery = $;

/**
 * jQuery extensies registreren zichzelf aan bovenstaande jQuery.
 */
require('bootstrap');
require('./ajax-csrf');
require('jquery-hoverintent');
require('jquery.scrollto');
require('jquery-ui');
require('jquery-ui/ui/effect');
require('jquery-ui/ui/effects/effect-highlight');
require('jquery-ui/ui/effects/effect-fade');
require('jquery-ui/ui/widgets/datepicker');
require('jquery-ui/ui/widgets/slider');
require('./lib/external/jquery.markitup');
require('./lib/external/jquery.contextMenu');
require('timeago');
require('raty-js');
require('autosize/build/jquery.autosize');
require('./lib/external/jquery.formSteps');
require('./lib/external/jquery-ui-sliderAccess');
require('jquery-ui-timepicker-addon');
require('./lib/external/jquery-ui-timepicker-nl');
require('jquery.maskedinput');
require('lightbox2');
require('corejs-typeahead/dist/typeahead.jquery.js');

/**
 * Globale objecten gebruikt in PHP code.
 */
$.extend(window, {
	Bloodhound,
	Dropzone,
	docReady,
	context: {
		// See view/groepen/leden/GroepTabView.class.php
		domUpdate,
		// See view/formulier/invoervelden/LidField.class.php
		init: (el: HTMLElement) => init(el),
	},
	courant: {
		// See templates/courant/courantbeheer.tpl
		importAgenda,
	},
	formulier: {
		// See view/formulier/invoervelden/InputField.abstract.php
		formCancel,
		// See templates/instellingen/beheer/instelling_row.tpl
		formInlineToggle,
		// See view/formulier/invoervelden/InputField.abstract.php
		// See view/formulier/invoervelden/ZoekField.class.php
		formSubmit,
		insertPlaatje,
	},
	forum: {
		// See blade_templates/forum/partial/post_lijst.blade.php
		forumBewerken,
		// See blade_templates/forum/partial/post_forum.blade.php
		saveConceptForumBericht,
	},
	// See resources/views/maaltijden/bb.blade.php
	ketzerAjax,
	maalcie: {
		// See view/maalcie/forms/SuggestieLijst.php
		takenColorSuggesties,
		// See assets/view/maaltijden/corveetaak/beheer_taken.blade.php
		takenShowOld,
		// See assets/view/maaltijden/corveetaak/beheer_taak_datum.blade.php
		// See assets/view/maaltijden/corveetaak/beheer_taak_head.blade.php
		takenToggleDatum,
		// See assets/view/maaltijden/corveetaak/suggesties_lijst.blade.php
		// See view/maalcie/forms/SuggestieLijst.php
		takenToggleSuggestie,
	},
});

Dropzone.autoDiscover = false;

$.timeago.settings.strings = {
	day: '1 dag',
	days: '%d dagen',
	hour: '1 uur',
	hours: '%d uur',
	minute: '1 minuut',
	minutes: '%d minuten',
	month: '1 maand',
	months: '%d maanden',
	numbers: [],
	prefiprefixAgo: '',
	prefixFromNow: 'sinds',
	seconds: 'nog geen minuut',
	suffixAgo: 'geleden',
	suffixFromNow: '',
	wordSeparator: ' ',
	year: '1 jaar',
	years: '%d jaar',
};

(async () => {
	await registerGrafiekContext();
	await registerFormulierContext();
	await registerGlobalContext();
	await registerKnopContext();
	await registerDataTableContext();
	await registerBbContext();

	docReady(() => {
		initSluitMeldingen();
		init(document.body);

		const modal = $('#modal');
		if (modal.html() !== '') {
			modal.modal();
		}
	});
})();

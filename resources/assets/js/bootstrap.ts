/**
 * Laad alle externe libs en knoop de goede dingen aan elkaar.
 */
import axios from 'axios';
import Bloodhound from 'corejs-typeahead';
import Dropzone from 'dropzone';
import $ from 'jquery';
import Popper from 'popper.js';
import Vue from 'vue';
import {ketzerAjax} from './ajax';
import {bbvideoDisplay} from './bbcode';
import {domUpdate} from './context';
import {importAgenda} from './courant';
import ctx, {init} from './ctx';
import {formCancel, formInlineToggle, formSubmit} from './formulier';
import {forumBewerken, saveConceptForumBericht} from './forum';
import {takenColorSuggesties, takenShowOld, takenToggleDatum, takenToggleSuggestie} from './maalcie';
import {docReady} from './util';

declare global {
	interface JQueryStatic {
		timeago: any;
	}

	interface JQuery {
		timeago: () => void;
		markItUp: (arg: any) => any;
		hoverIntent: (arg: any, arg1?: any) => any;
		autosize: () => void;
		scrollTo: (arg: any) => void;
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
require('./lib/jquery.markitup');
require('./lib/jquery.contextMenu');
require('timeago');
require('raty-js');
require('autosize/build/jquery.autosize');
require('./lib/jquery.formSteps');
require('./lib/jquery-ui-sliderAccess');
require('jquery-ui-timepicker-addon');
require('./lib/jquery-ui-timepicker-nl');
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
	bbcode: {
		// See view/bbcode/CsrBB.class.php
		bbvideoDisplay,
	},
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
		// See templates/maalcie/corveetaak/beheer_taken.tpl
		takenShowOld,
		// See templates/maalcie/corveetaak/beheer_taak_datum.tpl
		// See templates/maalcie/corveetaak/beheer_taak_head.tpl
		takenToggleDatum,
		// See templates/maalcie/corveetaak/suggesties_lijst.tpl
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

const kaartjes = {};

ctx.addHandlers({
	'.hoverIntent': (el) => $(el).hoverIntent({
		over() {
			$(this).find('.hoverIntentContent').fadeIn();
		},
		out() {
			$(this).find('.hoverIntentContent').fadeOut();
		},
		timeout: 250,
	}),
	'[data-visite]': (el: HTMLElement) => {

		const uid = el.dataset!.visite as string;
		if (!kaartjes.hasOwnProperty(uid)) {
			kaartjes[uid] = document.createElement('div');
			kaartjes[uid].style.zIndex = '1000';
		}
		let loading = false;
		let loaded = false;
		el.addEventListener('mouseenter', async () => {
			if (loading) {
				return;
			}

			el.append(kaartjes[uid]);
			// tslint:disable-next-line:no-unused-expression
			new Popper(el, kaartjes[uid], {placement: 'bottom-start'});

			loading = true;
			if (!loaded) {
				const kaartje = await axios.get(`/profiel/${el.dataset!.visite}/kaartje`);
				kaartjes[uid].innerHTML = kaartje.data;
				loaded = true;
			}
			loading = false;
		});
		el.addEventListener('mouseleave', () => {
			kaartjes[uid].remove();
		});
	},
	'.vue-context': (el) => new Vue({el}),
});

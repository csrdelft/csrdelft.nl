/**
 * Laad alle externe libs en knoop de goede dingen aan elkaar.
 */
import Bloodhound from 'corejs-typeahead';
import Dropzone from 'dropzone';
import $ from 'jquery';
import Vue from 'vue';
import {ketzerAjax} from './ajax';
import {bbvideoDisplay, CsrBBPreview} from './bbcode';
import {bbCodeSet} from './bbcode-set';
import {domUpdate} from './context';
import ctx from './ctx';
import {importAgenda} from './courant';
import {formCancel, formInlineToggle, formReset, formSubmit, formToggle} from './formulier';
import {forumBewerken, saveConceptForumBericht} from './forum';
import {knopGet, knopPost, knopVergroot} from './knop';
import {takenColorSuggesties, takenShowOld, takenToggleDatum, takenToggleSuggestie} from './maalcie';

declare global {
	interface JQueryStatic {
		timeago: any;
	}

	interface JQuery {
		timeago: () => void;
		markItUp: (arg: any) => any;
		uitooltip: (arg: any) => any;
		hoverIntent: (arg: any) => any;
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
require('jquery-ui/ui/widgets/tooltip');
require('jquery-ui/ui/widgets/tabs');
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

/**
 * Globale objecten gebruikt in PHP code.
 */
$.extend(window, {
	Bloodhound,
	Dropzone,
	bbcode: {
		// See view/formulier/invoervelden/BBCodeField.class.php
		// See templates/roodschopper/roodschopper.tpl
		// See templates/mededelingen/mededeling.tpl
		// See templates/courant/courantbeheer.tpl
		// See blade_templates/forum/partial/post_form.blade.php
		CsrBBPreview,
		// See view/bbcode/CsrBB.class.php
		bbvideoDisplay,
	},
	context: {
		// See view/groepen/leden/GroepTabView.class.php
		domUpdate,
		// See view/formulier/invoervelden/LidField.class.php
		initContext: ctx.initContext,
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
	// See templates/maalcie/maaltijd/maaltijd_ketzer.tpl
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

$.widget.bridge('uitooltip', $.ui.tooltip);

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

ctx.init({
	'': (el) => $(el).uitooltip({track: true}),
	'.InlineFormToggle': (el) => el.addEventListener('click.toggle', formToggle),
	'.SubmitChange': (el) => el.addEventListener('change.change', formSubmit),
	'.cancel': (el) => el.addEventListener('click.cancel', formCancel),
	'.get': (el) => el.addEventListener('click.get', knopGet),
	'.hoverIntent': (el) => $(el).hoverIntent({
		over() {
			$(this).find('.hoverIntentContent').fadeIn();
		},
		out() {
			$(this).find('.hoverIntentContent').fadeOut();
		},
		timeout: 250,
	}),
	'.post': (el) => el.addEventListener('click.post', knopPost),
	'.reset': (el) => el.addEventListener('click.reset', formReset),
	'.submit': (el) => el.addEventListener('click.submit', formSubmit),
	'.vergroot': (el) => el.addEventListener('click.vergroot', knopVergroot),
	'.vue-context': (el) => new Vue({el}),
	'form': (el) => el.addEventListener('submit', formSubmit),
	'textarea.BBCodeField': (el) => $(el).markItUp(bbCodeSet),
	'time.timeago': (el) => $(el).timeago(),
});

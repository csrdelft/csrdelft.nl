/**
 * Laad alle externe libs en knoop de goede dingen aan elkaar.
 */
import Bloodhound from 'corejs-typeahead';
import Dropzone from 'dropzone';
import $ from 'jquery';
import moment from 'moment';
import {
	registerBbContext,
	registerClipboardContext,
	registerDataTableContext, registerFlatpickrContext,
	registerFormulierContext,
	registerGlobalContext,
	registerGrafiekContext,
	registerKnopContext, registerLidInstellingenContext,
} from './context';
import {init} from './ctx';
import {ketzerAjax} from './lib/ajax';
import {importAgenda, importSponsor} from './lib/courant';
import {initSluitMeldingen} from './lib/csrdelft';
import {domUpdate} from './lib/domUpdate';
import {formCancel, formInlineToggle, formSubmit} from './lib/formulier';
import {forumBewerken, saveConceptForumBericht} from './lib/forum';
import {takenColorSuggesties, takenShowOld, takenToggleDatum, takenToggleSuggestie} from './lib/maalcie';
import {docReady, isLoggedIn} from './lib/util';
import {Modal} from "bootstrap";

moment.locale('nl');

window.$ = window.jQuery = $;

/**
 * jQuery extensies registreren zichzelf aan bovenstaande jQuery.
 */
require('bootstrap');
require('./ajax-csrf');
require('jquery.scrollto');
require('jquery-ui');
require('jquery-ui/ui/effect');
require('jquery-ui/ui/effects/effect-highlight');
require('jquery-ui/ui/effects/effect-fade');
require('jquery-ui/ui/widgets/slider');
require('./lib/external/jquery.contextMenu');
require('raty-js');
require('jquery.maskedinput');
require('fslightbox');
require('corejs-typeahead/dist/typeahead.jquery.js');

declare global {
	interface Window {
		loggedIn: boolean
	}
}

/**
 * Globale objecten gebruikt in PHP code.
 */
$.extend(window, {
	loggedIn: isLoggedIn(),
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
		importSponsor,
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
		// See templates/forum/partial/post_lijst.html.twig
		forumBewerken,
		// See templates/forum/partial/post_forum.html.twig
		saveConceptForumBericht,
	},
	// See templates/maaltijden/bb.html.twig
	ketzerAjax,
	maalcie: {
		// See view/maalcie/forms/SuggestieLijst.php
		takenColorSuggesties,
		// See templates/maaltijden/corveetaak/beheer_taken.html.twig
		takenShowOld,
		// See templates/maaltijden/corveetaak/beheer_taak_datum.html.twig
		// See templates/maaltijden/corveetaak/beheer_taak_head.html.twig
		takenToggleDatum,
		// See templates/maaltijden/corveetaak/suggesties_lijst.html.twig
		// See view/maalcie/forms/SuggestieLijst.php
		takenToggleSuggestie,
	},
});

Dropzone.autoDiscover = false;

(async () => {
	await Promise.all([
		registerClipboardContext(),
		registerGrafiekContext(),
		registerFormulierContext(),
		registerGlobalContext(),
		registerKnopContext(),
		registerDataTableContext(),
		registerBbContext(),
		registerFlatpickrContext(),
		registerLidInstellingenContext(),
	]);

	docReady(() => {
		window.refreshFsLightbox();
		initSluitMeldingen();
		init(document.body);
		const modalEl = document.getElementById("modal")
		if (modalEl && modalEl.innerHTML !== '') {
			const modal = new Modal(modalEl);
			modal.show();
		}
	});
})();

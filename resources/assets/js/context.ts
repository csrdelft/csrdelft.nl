import $ from 'jquery';
import Vue from 'vue';
import ctx from './ctx';
import {initDataTable, initOfflineDataTable} from './datatable/api';
import {formCancel, formReset, formSubmit, formToggle} from './formulier';
import {initKaartjes} from './kaartje';
import {initBbPreview, initBbPreviewBtn, loadBbImage} from './lib/bbcode';
import {activeerLidHints} from './lib/bbcode-hints';
import {bbCodeSet} from './lib/bbcode-set';
import {knopGet, knopPost, knopVergroot} from './lib/knop';
import {reloadAgendaHandler} from './page/agenda';

export const registerGrafiekContext = async () => {
	const {
		initBar,
		initDeelnamegrafiek,
		initLine,
		initPie,
		initSaldoGrafiek,
	} = await import(/* webpackChunkName: "grafiek" */'./grafiek');

	ctx.addHandlers({
		'.ctx-deelnamegrafiek': initDeelnamegrafiek,
		'.ctx-graph-bar': initBar,
		'.ctx-graph-line': initLine,
		'.ctx-graph-pie': initPie,
		'.ctx-saldografiek': initSaldoGrafiek,
	});
};

export const registerBbContext = async () => {
	ctx.addHandlers({
		'div.bb-img-loading': loadBbImage,
		'[data-bbpreview-btn]': initBbPreviewBtn,
		'[data-bbpreview]': initBbPreview,
		'textarea.BBCodeField': activeerLidHints,
	});
};

export const registerDataTableContext = async () => {
	ctx.addHandlers({
		'.ctx-datatable': initDataTable,
		'.ctx-offline-datatable': initOfflineDataTable,
	});
};

export const registerKnopContext = async () => {
	ctx.addHandlers({
		'.get': (el) => el.addEventListener('click', knopGet),
		'.post': (el) => el.addEventListener('click', knopPost),
		'.vergroot': (el) => el.addEventListener('click', knopVergroot),
		'[data-buttons=radio]': (el) => {
			for (const btn of Array.from(el.querySelectorAll('a.btn'))) {
				btn.addEventListener('click',
					(event) => {
						for (const active of Array.from(el.querySelectorAll('.active'))) {
							active.classList.remove('active');
						}
						(event.target as Element).classList.add('active');
					},
				);
			}
		},
	});

};

export const registerAgendaContext = async () => {
	ctx.addHandler('.ReloadAgenda', reloadAgendaHandler);
};

export const registerFormulierContext = async () => {
	ctx.addHandlers({
		'.InlineFormToggle': (el) => el.addEventListener('click', (event) => formToggle(el, event)),
		'.SubmitChange': (el) => el.addEventListener('change', formSubmit),
		'.cancel': (el) => el.addEventListener('click', formCancel),
		'.reset': (el) => el.addEventListener('click', formReset),
		'.submit': (el) => el.addEventListener('click', formSubmit),
		'form.Formulier': (el) => $(el).on('submit', formSubmit), // dit is sterker dan addEventListener
		'textarea.BBCodeField': (el) => $(el).markItUp(bbCodeSet),
		'time.timeago': (el) => $(el).timeago(),
	});
};

export const registerGlobalContext = async () => {
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
		'.vue-context': (el) => new Vue({el}),
		'[data-visite]': initKaartjes,
	});
};

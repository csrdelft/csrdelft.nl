import ctx from './ctx';
import hoverintent from "hoverintent";
import {select} from "./lib/dom";

export const registerGrafiekContext = async (): Promise<void> => {
	const {
		initBar,
		initDeelnamegrafiek,
		initLine,
		initPie,
		initSaldoGrafiek,
	} = await import(/* webpackChunkName: "grafiek" */'./lib/grafiek');

	ctx.addHandlers({
		'.ctx-deelnamegrafiek': initDeelnamegrafiek,
		'.ctx-graph-bar': initBar,
		'.ctx-graph-line': initLine,
		'.ctx-graph-pie': initPie,
		'.ctx-saldografiek': initSaldoGrafiek,
	});
};

export const registerBbContext = async (): Promise<void> => {
	const {
		activeerLidHints,
		initBbPreview,
		initBbPreviewBtn,
		loadBbImage,
	} = await import(/* webpackChunkName: "bbcode" */'./lib/bbcode');

	ctx.addHandlers({
		'div.bb-img-loading': loadBbImage,
		'[data-bbpreview-btn]': initBbPreviewBtn,
		'[data-bbpreview]': initBbPreview,
		'textarea.BBCodeField': activeerLidHints,
	});
};

export const registerDataTableContext = async (): Promise<void> => {
	const {
		initDataTable,
		initOfflineDataTable,
	} = await import(/* webpackChunkName: "datatable-api" */'./datatable/api');

	ctx.addHandlers({
		'.ctx-datatable': initDataTable,
		'.ctx-offline-datatable': initOfflineDataTable,
	});
};

export const registerKnopContext = async (): Promise<void> => {
	const {
		knopGet,
		knopPost,
		knopVergroot,
	} = await import(/* webpackChunkName: "knop" */'./lib/knop');

	ctx.addHandlers({
		'.get': (el) => el.addEventListener('click', (e) => knopGet(e, el)),
		'.post': (el) => el.addEventListener('click', knopPost),
		'.vergroot': (el) => el.addEventListener('click', (e) => knopVergroot(e, el)),
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

export const registerFormulierContext = async (): Promise<void> => {
	const [
		{
			formCancel,
			formReset,
			formSubmit,
			formToggle,
		},
		{
			bbCodeSet,
		},
	] = await Promise.all([
		import(/* webpackChunkName: "formulier" */'./lib/formulier'),
		import(/* webpackChunkName: "bbcode-set" */'./lib/bbcode-set'),
	]);

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

export const registerGlobalContext = async (): Promise<void> => {
	const [
		{initKaartjes},
		{default: Vue},
		{default: $},
	] = await Promise.all([
		import(/* webpackChunkName: "kaartje" */'./lib/kaartje'),
		import(/* webpackChunkName: "vue" */'vue'),
		import(/* webpackChunkName: "jquery" */'jquery'),
	]);

	ctx.addHandlers({
		'.hoverIntent': (el) => hoverintent(el,
			() => $(select('.hoverIntentContent', el)).fadeIn(),
			() => $(select('.hoverIntentContent', el)).fadeOut()
		).options({timeout: 250}),
		'.vue-context': (el) => new Vue({el}),
		'[data-visite]': initKaartjes,
		'.AutoSize': el => {
				el.setAttribute('style', 'height:' + (el.scrollHeight) + 'px;overflow-y:hidden;');
				el.addEventListener("input", function () {
					this.style.height = 'auto';
					this.style.height = (this.scrollHeight) + 'px';
				}, false);
		}
	});
};

export const registerFlatpickrContext = async (): Promise<void> => {
	const {
		initDateTimePicker,
	} = await import(/* webpackChunkName: "datepicker" */'./lib/datepicker');

	ctx.addHandlers({
		'.DateTimeField': initDateTimePicker,
	});
};

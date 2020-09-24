import ctx from './ctx';
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
		initKnopPost,
		initKnopGet,
		initKnopVergroot,
		initRadioButtons,
	} = await import(/* webpackChunkName: "knop" */'./lib/knop');

	ctx.addHandlers({
		'.get': initKnopGet,
		'.post': initKnopPost,
		'.vergroot': initKnopVergroot,
		'[data-buttons=radio]': initRadioButtons,
	});

};

export const registerFormulierContext = async (): Promise<void> => {
	const [
		{
			formCancel,
			formReset,
			formSubmit,
			formToggle,
			initSterrenField,
		},
		{
			bbCodeSet,
		},
		{
			initDropzone,
		},
	] = await Promise.all([
		import(/* webpackChunkName: "formulier" */'./lib/formulier'),
		import(/* webpackChunkName: "bbcode-set" */'./lib/bbcode-set'),
		import(/* webpackChunkName: "dropzone" */'./lib/dropzone'),
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
		'.SterrenField': initSterrenField,
		'form.dropzone': initDropzone,
	});
};

export const registerGlobalContext = async (): Promise<void> => {
	const [
		{default: hoverintent},
		{initKaartjes},
		{default: Vue},
		{default: $},
	] = await Promise.all([
		import(/* webpackChunkName: "hoverintent" */'hoverintent'),
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
			const cb = function () {
				el.style.height = 'auto';
				el.style.height = (el.scrollHeight) + 'px';
			}
			el.setAttribute('style', 'height:' + (el.scrollHeight) + 'px;overflow-y:hidden;');
			el.addEventListener("input", cb, false);
			setTimeout(cb)
		}
	});
};

export const registerFlatpickrContext = async (): Promise<void> => {
	const {
		initDateTimePicker,
		initDatePicker,
	} = await import(/* webpackChunkName: "datepicker" */'./lib/datepicker');

	ctx.addHandlers({
		'.DateTimeField': initDateTimePicker,
		'.DateField': initDatePicker,
	});
};

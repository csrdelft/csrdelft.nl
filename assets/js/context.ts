import ctx from './ctx';
import { select } from './lib/dom';
import { autosizeTextarea } from './lib/util';

export const registerClipboardContext = async (): Promise<void> => {
	const { copyTextToClipboard } = await import('./lib/clipboard');

	ctx.addHandler('[data-copy]', (el) => {
		el.addEventListener('click', () => copyTextToClipboard(el.dataset.copy));
	});
};

export const registerGrafiekContext = async (): Promise<void> => {
	const { initBar, initDeelnamegrafiek, initLine, initPie, initSaldoGrafiek } =
		await import('./lib/grafiek');

	ctx.addHandlers({
		'.ctx-deelnamegrafiek': initDeelnamegrafiek,
		'.ctx-graph-bar': initBar,
		'.ctx-graph-line': initLine,
		'.ctx-graph-pie': initPie,
		'.ctx-saldografiek': initSaldoGrafiek,
	});
};

export const registerBbContext = async (): Promise<void> => {
	const { loadBbImage } = await import('./lib/bbcode');

	ctx.addHandlers({
		'.bb-img-loading': loadBbImage,
	});
};

export const registerDataTableContext = async (): Promise<void> => {
	const { initDataTable, initOfflineDataTable } = await import(
		'./datatable/api'
	);

	ctx.addHandlers({
		'.ctx-datatable': initDataTable,
		'.ctx-offline-datatable': initOfflineDataTable,
	});
};

export const registerKnopContext = async (): Promise<void> => {
	const { initKnopPost, initKnopGet, initKnopVergroot, initRadioButtons } =
		await import('./lib/knop');

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
			initDoctrineField,
			initAutocompleteField,
		},
		{ initDropzone },
		{ initTimeago },
		{ default: $ },
	] = await Promise.all([
		import('./lib/formulier'),
		import('./lib/dropzone'),
		import('./lib/timeago'),
		import('jquery'),
	]);

	ctx.addHandlers({
		'.InlineFormToggle': (el) =>
			el.addEventListener('click', (event) => formToggle(el, event)),
		'.SubmitChange': (el) => el.addEventListener('change', formSubmit),
		'.cancel': (el) => el.addEventListener('click', formCancel),
		'.reset': (el) => el.addEventListener('click', formReset),
		'.submit': (el) => el.addEventListener('click', formSubmit),
		'form.Formulier': (el) => $(el).on('submit', formSubmit), // dit is sterker dan addEventListener
		'time.timeago': initTimeago,
		'.SterrenField': initSterrenField,
		'form.dropzone': initDropzone,
		'.doctrine-field': initDoctrineField,
		'.autocomplete-field': initAutocompleteField,
	});
};

export const registerGlobalContext = async (): Promise<void> => {
	const [
		{ default: hoverintent },
		{ initKaartjes },
		{ createDefaultApp, getVueComponent },
		{ default: $ },
	] = await Promise.all([
		import('hoverintent'),
		import('./lib/kaartje'),
		import('./register-vue'),
		import('jquery'),
	]);

	ctx.addHandlers({
		'.hoverIntent': (el) =>
			hoverintent(
				el,
				() => $(select('.hoverIntentContent', el)).fadeIn(),
				() => $(select('.hoverIntentContent', el)).fadeOut()
			).options({ timeout: 250 }),
		'.vue-component': (el) =>
			createDefaultApp(
				getVueComponent(el.dataset.naam),
				JSON.parse(el.dataset.props)
			).mount(el),
		'[data-visite]': initKaartjes,
		'.AutoSize': autosizeTextarea,
	});
};

export const registerLidInstellingenContext = async (): Promise<void> => {
	const { instellingOpslaan } = await import('./page/instellingen');

	ctx.addHandler('.instellingKnop', (el) =>
		el.addEventListener('click', instellingOpslaan)
	);
	ctx.addHandler('.change-opslaan', (el) =>
		el.addEventListener('change', instellingOpslaan)
	);
};

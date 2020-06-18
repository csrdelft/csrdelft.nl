import ctx from './ctx';

export const registerGrafiekContext = async () => {
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

export const registerBbContext = async () => {
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

export const registerDataTableContext = async () => {
	const {
		initDataTable,
		initOfflineDataTable,
	} = await import(/* webpackChunkName: "datatable-api" */'./datatable/api');

	ctx.addHandlers({
		'.ctx-datatable': initDataTable,
		'.ctx-offline-datatable': initOfflineDataTable,
	});
};

export const registerKnopContext = async () => {
	const {
		knopGet,
		knopPost,
		knopVergroot,
	} = await import(/* webpackChunkName: "knop" */'./lib/knop');

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

export const registerFormulierContext = async () => {
	const {
		formCancel,
		formReset,
		formSubmit,
		formToggle,
	} = await import(/* webpackChunkName: "formulier" */'./lib/formulier');
	const {bbCodeSet} = await import(/* webpackChunkName: "bbcode-set" */'./lib/bbcode-set');

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
	const {initKaartjes} = await import(/* webpackChunkName: "kaartje" */'./lib/kaartje');
	const {default: Vue} = await import(/* webpackChunkName: "vue" */'vue');
	const {default: $} = await import(/* webpackChunkName: "jquery" */'jquery');

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

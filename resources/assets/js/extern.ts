import './ajax-csrf';
import {docReady} from './util';

declare global {
	interface Window {
		$: JQueryStatic;
		jQuery: JQueryStatic;
		formulier: Formulier;
		docReady: (fn: () => void) => void;
	}

	interface Formulier {
		formSubmit(event: Event): void;
	}
}

window.docReady = docReady;

// Versimpelde versie van formSubmit in formulier.js
window.formulier = {formSubmit: (event) => (event.target as HTMLFormElement).form.submit()};

window.docReady(() => {
	setTimeout(() => document.body.classList.remove('is-loading'));
	import('jquery').then(($) => {
		window.$ = window.jQuery = $.default;

		import(/* webpackChunkName: "extern-defer" */ './extern-defer');
	});
});

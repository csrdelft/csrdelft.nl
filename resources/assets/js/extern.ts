import $ from 'jquery';

declare global {
	interface Window {
		$: JQueryStatic
		jQuery: JQueryStatic
		formulier: Formulier
	}

	interface Formulier {
		formSubmit(event: Event) : void
	}
}

window.$ = window.jQuery = $;

// Versimpelde versie van formSubmit in formulier.js
window.formulier = {formSubmit: (event) => (event.target as HTMLFormElement).form.submit()};

$(function () {
	$('body').removeClass('is-loading');

	import(/* webpackChunkName: "extern-defer" */ './extern-defer');
});

import $ from 'jquery';

window.$ = window.jQuery = $;

// Versimpelde versie van formSubmit in formulier.js
window.formulier = {formSubmit: (event) => event.target.form.submit()};

$(function () {
	$('body').removeClass('is-loading');

	import(/* webpackChunkName: "extern-defer" */ './extern-defer');
});

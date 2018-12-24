import $ from 'jquery';

window.$ = window.jQuery = $;

$(function () {
	$('body').removeClass('is-loading');

	import('./extern-defer');
});

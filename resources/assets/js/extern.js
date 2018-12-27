import $ from 'jquery';

window.$ = window.jQuery = $;

// jgallery is op page load nodig
require('jgallery/dist/js/jgallery');

$(function () {
	$('body').removeClass('is-loading');

	import('./extern-defer');
});

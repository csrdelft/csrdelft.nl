jQuery(document).ready(function () {
	var $iframe = jQuery('#wikiframe');
	jQuery(window).resize(function () {
		$iframe.css({
			'height': window.innerHeight - 3
		});
	});
	jQuery(document).trigger('resize');
});
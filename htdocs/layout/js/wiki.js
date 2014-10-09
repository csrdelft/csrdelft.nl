jQuery(document).ready(function () {
	var scrollbar = getScrollBarWidth();
	var $iframe = jQuery('#wikiframe');
	jQuery(window).resize(function () {
		$iframe.css({
			'height': window.innerHeight - 2
		});
	});
	jQuery(document).trigger('resize');
});
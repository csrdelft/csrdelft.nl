/**
 * Wordt geladen als de pagina geladen is.
 */
import $ from 'jquery';
import {bbvideoDisplay, CsrBBPreview} from './bbcode';
import {init} from './ctx';
import './formulier';
import './fotoalbum/FotoAlbum';

require('lightbox2');
require('./lib/jquery.markitup');
require('jquery-ui/ui/widgets/tooltip');
require('jquery-hoverintent');

$.widget.bridge('uitooltip', $.ui.tooltip);

require('timeago');

declare global {
	// Deze functie heeft geen type...
	namespace JQueryUI {
		interface Widget {
			bridge: (newName: string, widget: Widget) => void;
		}
	}
	interface Window {
		bbcode: any;
	}
}

window.bbcode = {
	CsrBBPreview,
	bbvideoDisplay,
};

const $window = $(window);
const $header = $('#header');
let $banner = $('#banner');

if (typeof $banner[0] === 'undefined') {
	$banner = $('#banner-small');
}

$window.on('load', () => {
	// Lazy load cms pages, these should be loaded always, not on scroll
	setTimeout(() => {
		$('div.bb-img-loading').each(function () {
			const content = $(document.createElement('img'));
			content.on('error', function () {
				$(this).attr('title', 'Afbeelding bestaat niet of is niet toegankelijk!');
				$(this).attr('src', '/plaetjes/famfamfam/picture_error.png');
				$(this).css('width', '16px');
				$(this).css('height', '16px');
				$(this).removeClass('bb-img-loading').addClass('bb-img');
			});
			content.addClass('bb-img');
			content.attr('alt', $(this).attr('title')!);
			content.attr('style', $(this).attr('style')!);
			content.attr('src', $(this).attr('src')!);
			$(this).html(content.toString());
			content.on('load', function () {
				const foto = content.attr('src')!.indexOf('/plaetjes/fotoalbum/') >= 0;
				const video = $(this).parent().parent().hasClass('bb-video-preview');
				const hasAnchor = $(this).closest('a').length !== 0;
				$(this).parent().replaceWith($(this));
				if (!foto && !video && !hasAnchor) {
					$(this).wrap(`<a class="lightbox-link" href="${$(this).attr('src')}" data-lightbox="page-lightbox"></a>`);
				}
			});
		});
	});
});

const lazyLoad = (() => {
	let hasLoaded = false;

	return () => {
		if (hasLoaded) {
			return;
		}

		hasLoaded = true;

		// Lazy load frontpage
		setTimeout(() => {
			$('.lazy-load').each(function () {
				$(this).replaceWith(this.textContent!);
			});
		});
	};
})();

// Lazy load after animations have finished and user has scrolled
$window.on('scroll', () => {
	if ($(window).scrollTop()! > 0) {
		lazyLoad();
	}

	if (window.pageYOffset > $banner.outerHeight()!) {
		$header.removeClass('alt');
	} else {
		$header.addClass('alt');
	}
});

$window.on('resize', () => $window.trigger('scroll'));
$window.trigger('scroll');

init(document.body);

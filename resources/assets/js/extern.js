import $ from 'jquery';

window.$ = window.jQuery = $;

import 'jquery.scrollex';
import 'lightbox2';

$(function () {

	let $window = $(window),
		$body = $('body'),
		$header = $('#header'),
		$banner = $('#banner');

	let hasLoaded = false;

	if (typeof $banner[0] === 'undefined') {
		$banner = $('#banner-small');
	}

	$window.on('load', function () {
		$body.removeClass('is-loading');

		// Lazy load cms pages, these should be loaded always, not on scroll
		setTimeout(function () {
			$('div.bb-img-loading').each(function () {
				const content = $(document.createElement('img'));
				content.error(function () {
					$(this).attr('title', 'Afbeelding bestaat niet of is niet toegankelijk!');
					$(this).attr('src', '/plaetjes/famfamfam/picture_error.png');
					$(this).css('width', '16px');
					$(this).css('height', '16px');
					$(this).removeClass('bb-img-loading').addClass('bb-img');
				});
				content.addClass('bb-img');
				content.attr('alt', $(this).attr('title'));
				content.attr('style', $(this).attr('style'));
				content.attr('src', $(this).attr('src'));
				$(this).html(content);
				content.on('load', function () {
					const foto = content.attr('src').indexOf('/plaetjes/fotoalbum/') >= 0;
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

	function lazyLoad() {
		if (hasLoaded === true) {
            return;
        }

		hasLoaded = true;

		// Lazy load frontpage
		setTimeout(function () {
            $('.lazy-load').each(function () {
				const html = $(this).data('lazy');
				$(this).data('lazy', '');
				$(this).append(html);
			});
		});
	}

	// Lazy load after animations have finished and user has scrolled
	$window.scroll(() => {
		if (hasLoaded === false && $(window).scrollTop() > 0) {
			lazyLoad();
		}
	});

	if ($banner.length > 0 && $header.hasClass('alt')) {

		$window.on('resize', () => $window.trigger('scroll'));

		$banner.scrollex({
			bottom: $header.outerHeight(),
			terminate: () => $header.removeClass('alt'),
			enter: () => $header.addClass('alt'),
			leave: () => $header.removeClass('alt')
        });

	}
});

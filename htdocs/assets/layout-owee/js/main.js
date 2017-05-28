/*
 Solid State by HTML5 UP
 html5up.net | @n33co
 Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
 */

(function ($) {

    "use strict";

    $(function () {

        var $window = $(window),
            $body = $('body'),
            $header = $('#header'),
            $banner = $('#banner');

        var hasLoaded = false;

        if (typeof $banner[0] == "undefined") {
            $banner = $('#banner-small');
        }

        $window.on('load', function () {
            $body.removeClass('is-loading');

			// Lazy load cms pages, these should be loaded always, not on scroll
			setTimeout(function() {
				$('div.bb-img-loading').each(function () {
					var content = $(document.createElement('IMG'));
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
						var foto = content.attr('src').indexOf('/plaetjes/fotoalbum/') >= 0;
						var video = $(this).parent().parent().hasClass('bb-video-preview');
						var hasAnchor = $(this).closest('a').length !== 0;
						$(this).parent().replaceWith($(this));
						if (!foto && !video && !hasAnchor) {
							$(this).wrap('<a class="lightbox-link" href="' + $(this).attr('src') + '" data-lightbox="page-lightbox"></a>');
						}
					});
				});
			});
        });

        // Lazy load after animations have finished and user has scrolled
        $window.scroll(function() {
            if (hasLoaded === false && $(window).scrollTop() > 0) {
                lazyLoad();
            }
        });

        function lazyLoad() {
            if (hasLoaded === true) return;
            hasLoaded = true;

            // Lazy load frontpage
            setTimeout(function() {
                $('.lazy-load').each(function() {
                    var html = $(this).data('lazy');
                    $(this).append(html);
                });
            });
        }

        if ($banner.length > 0
            && $header.hasClass('alt')) {

            $window.on('resize', function () {
                $window.trigger('scroll');
            });

            $banner.scrollex({
                bottom: $header.outerHeight(),
                terminate: function () {
                    $header.removeClass('alt');
                },
                enter: function () {
                    $header.addClass('alt');
                },
                leave: function () {
                    $header.removeClass('alt');
                }
            });

        }
    });

})(jQuery);

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

        // Disable animations/transitions until the page has loaded.
        $body.addClass('is-loading');

        $window.on('load', function () {
            $body.removeClass('is-loading');
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

            setTimeout(function() {
                $('.lazy-load').each(function() {
                    var html = $(this).data('lazy');
                    $(this).append(html);
                });
            }, 1000);
        }

        // Fix: Placeholder polyfill.
        $('form').placeholder();

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

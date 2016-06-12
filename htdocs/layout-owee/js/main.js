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

        if (typeof $banner[0] == "undefined") {
            $banner = $('#banner-small');
        }

        // Disable animations/transitions until the page has loaded.
        $body.addClass('is-loading');

        $window.on('load', function () {
            window.setTimeout(function () {
                $body.removeClass('is-loading');
            }, 100);
        });

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

        // Menu.
        var $menu = $('#menu');

        $menu._locked = false;

        $menu._lock = function () {

            if ($menu._locked)
                return false;

            $menu._locked = true;

            window.setTimeout(function () {
                $menu._locked = false;
            }, 350);

            return true;

        };

        $menu._show = function () {

            if ($menu._lock())
                $body.addClass('is-menu-visible');

        };

        $menu._hide = function () {

            if ($menu._lock())
                $body.removeClass('is-menu-visible');

        };

        $menu._toggle = function () {

            if ($menu._lock())
                $body.toggleClass('is-menu-visible');

        };

        $menu
            .appendTo($body)
            .on('click', function (event) {

                event.stopPropagation();

                // Hide.
                $menu._hide();

            })
            .find('.inner')
            .on('click', '.close', function (event) {

                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();

                // Hide.
                $menu._hide();

            })
            .on('click', function (event) {
                event.stopPropagation();
            })
            .on('click', 'a', function (event) {

                var href = $(this).attr('href');

                event.preventDefault();
                event.stopPropagation();

                // Hide.
                $menu._hide();

                // Redirect.
                window.setTimeout(function () {
                    window.location.href = href;
                }, 350);

            });

        var $login = $('#login');

        $login._locked = false;

        $login._lock = function () {

            if ($login._locked)
                return false;

            $login._locked = true;

            window.setTimeout(function () {
                $login._locked = false;
            }, 350);

            return true;

        };

        $login._show = function () {

            if ($login._lock())
                $body.addClass('is-login-visible');

        };

        $login._hide = function () {

            if ($login._lock())
                $body.removeClass('is-login-visible');

        };

        $login._toggle = function () {

            if ($login._lock())
                $body.toggleClass('is-login-visible');

        };

        $login
            .appendTo($body)
            .on('click', function (event) {

                event.stopPropagation();

                // Hide.
                $login._hide();

            })
            .find('.inner')
            .on('click', '.close', function (event) {

                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();

                // Hide.
                $login._hide();

            })
            .on('click', function (event) {
                event.stopPropagation();
            })

        $body
            .on('click', 'a[href="#menu"]', function (event) {

                event.stopPropagation();
                event.preventDefault();

                // Toggle.
                $menu._toggle();

            })
            .on('click', 'a[href="#login"]', function (event) {
                event.stopPropagation();
                event.preventDefault();

                $login._toggle();
            })
            .on('keydown', function (event) {

                // Hide on escape.
                if (event.keyCode == 27) {
                    $menu._hide();
                    $login._hide();
                }

            });

    });

})(jQuery);

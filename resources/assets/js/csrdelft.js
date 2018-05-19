/**
 * csrdelft.nl javascript libje...
 */

import $ from 'jquery';

import {modalOpen} from './modal';
import initContext from './context';

function preloadImg(href) {
    let img = $(document.createElement('img'));
    img[0].src = href;
    return img;
}

preloadImg('/images/loading-fb.gif');
preloadImg('/images/loading-arrows.gif');
preloadImg('/images/loading_bar_black.gif');

$(() => {
    zijbalkScrollFixed();
    initTooltipOnce();
    initSluitMeldingen();
    initContext($('body'));
    //initGeolocation();
});

function initTooltipOnce() {
    // Change JQueryUI/tooltip plugin name to 'uitooltip' to fix name collision with Bootstrap/tooltip
    $.widget.bridge('uitooltip', $.ui.tooltip);
}

function initGeolocation() {

    let prev_pos = false;

    const position_save = function (position) {
        if (!prev_pos || ($(prev_pos.coords).not(position.coords).length === 0 && $(position.coords).not(prev_pos.coords).length === 0)) {
            prev_pos = position;
            $.post('/geolocation/save', {
                coords: position.coords,
                timestamp: Math.round(position.timestamp / 1000)
            });
        }
    };

    const position_error = function (error) {
        switch (error.code) {
            case error.PERMISSION_DENIED:
                break;
            case error.POSITION_UNAVAILABLE:
                break;
            case error.TIMEOUT:
                break;
            case error.UNKNOWN_ERROR:
                break;
        }
    };

    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(position_save, position_error);
    }
}

function initSluitMeldingen() {
    $('#melding').on('click', '.alert', function () {
        $(this).slideUp(400, function () {
            $(this).remove();
        });
    });
}

function zijbalkScrollFixed() {
    let elmnt = $('#cd-zijbalk');
    if (!elmnt.length || !elmnt.hasClass('scroll-fixed')) {
        return;
    }

    if (elmnt.hasClass('desktop-only') && /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        elmnt.removeClass('desktop-only scroll-fixed dragobject dragvertical scroll-hover');
        return;
    }

    // adjust to container size
    $(window).resize(function () {
        elmnt.css('height', document.documentElement.clientHeight);
    });
    $(window).trigger('resize');

    // fix position on screen
    $(window).scroll(function () {
        elmnt.css({
            'top': $(window).scrollTop()
        });
    });

    // set scroll position
    elmnt.scrollTop(elmnt.attr('data-scrollfix'));

    // remember scroll position
    let trigger = false;
    const saveCoords = function () {
        $.post('/tools/dragobject', {
            id: 'zijbalk',
            coords: {
                top: elmnt.scrollTop(),
                left: elmnt.scrollLeft()
            }
        });
        trigger = false;
    };
    elmnt.scroll(function () {
        if (!trigger) {
            trigger = true;
            $(window).one('mouseup', saveCoords);
        }
    });

    // show-hide scrollbar
    if (elmnt.hasClass('scroll-hover')) {
        const showscroll = function () {
            if (elmnt.get(0).scrollHeight > elmnt.get(0).clientHeight) {
                elmnt.css({
                    'overflow-y': 'scroll'
                });
            }
        };
        const hidescroll = function () {
            elmnt.css({
                'overflow-y': 'hidden'
            });
        };
        elmnt.hover(showscroll, hidescroll);
    }
}

/**
 * @see templates/fotoalbum/album.tpl
 * @param htmlString
 */
window.page_reload = function(htmlString) {
    // prevent hidden errors
    if (typeof htmlString === 'string' && htmlString.substring(0, 16) === '<div id="modal" ') {
        modalOpen(htmlString);
        return;
    }
    location.reload();
};

/**
 * @see templates/fotoalbum/album.tpl
 * @param htmlString
 */
window.page_redirect = function(htmlString) {
    // prevent hidden errors
    if (typeof htmlString === 'string' && htmlString.substring(0, 16) === '<div id="modal" ') {
        modalOpen(htmlString);
        return;
    }
    window.location.href = htmlString;
};

/**
 * @see templates/maalcie/maaltijd/maaltijd_ketzer.tpl
 * @param url
 * @param ketzer
 * @returns {boolean}
 */
window.ketzer_ajax = function(url, ketzer) {
    $(ketzer + ' .aanmelddata').html('Aangemeld:<br /><img src="/images/loading-arrows.gif" />');
    let jqXHR = $.ajax({
        type: 'GET',
        cache: false,
        url: url,
        data: ''
    });
    jqXHR.done(function (data) {
        $(ketzer).replaceWith(data);
    });
    jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
        $(ketzer + ' .aanmelddata').html('<span class="error">Error: </span>' + errorThrown);
        alert(errorThrown);
    });
    return true;
};

/**
 * @see templates/peiling/peiling.bb.tpl
 * @param peiling
 */
window.peiling_bevestig_stem = function(peiling) {
    let id = $('input[name=optie]:checked', peiling).val();
    let waarde = $(`#label${id}`).text();
    if (waarde.length > 0 && confirm('Bevestig uw stem:\n\n' + waarde + '\n\n')) {
        $(peiling).submit();
    }
};

/**
 * @see templates/courant/courantbeheer.tpl
 * @param id
 */
window.importAgenda = (id) => {
    let jqXHR = $.ajax({
        type: 'POST',
        cache: false,
        url: '/agenda/courant/',
        data: ''
    });
    jqXHR.done(data => document.getElementById(id).value += '\n' + data);
};

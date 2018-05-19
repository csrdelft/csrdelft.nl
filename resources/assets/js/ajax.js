import {modalClose, modalOpen} from './modal';

/**
 * @see maalcie.js
 * @param type
 * @param url
 * @param data
 * @param source
 * @param onsuccess
 * @param onerror
 * @param onfinish
 */
export const ajaxRequest = (type, url, data, source, onsuccess, onerror, onfinish) => {
    if (source) {
        if (!source.hasClass('noanim')) {
            $(source).replaceWith(`<img id="${source.attr('id')}" title="${url}" src="/images/loading-arrows.gif" />`);
            source = `img[title="${url}"]`;
        }
        else if (source.hasClass('InlineForm')) {
            $(source).find('.FormElement:first').css({
                'background-image': 'url("/images/loading-fb.gif")',
                'background-repeat': 'no-repeat',
                'background-position': 'center right'
            });
        }
    }
    else {
        modalOpen();
    }
    let contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
    let processData = true;
    if (data instanceof FormData) {
        contentType = false;
        processData = false;
    }
    $.ajax({
        type,
        cache: false,
        contentType,
        processData,
        url,
        data
    }).done(function (data) {
        if (source) {
            if (!$(source).hasClass('noanim')) {
                $(source).hide();
            }
            else if ($(source).hasClass('InlineForm')) {
                $(source).find('.FormElement:first').css({
                    'background-image': '',
                    'background-repeat': '',
                    'background-position': ''
                });
            }
        }
        onsuccess(data);
    }).fail(function (data, textStatus, errorThrown) {
        if (errorThrown === '') {
            errorThrown = 'Nog bezig met laden!';
        }
        if (source) {
            $(source).replaceWith('<img title="' + errorThrown + '" src="/plaetjes/famfamfam/cancel.png" />');
        }
        else {
            modalClose();
        }
        if (onerror) {
            onerror(data.responseText);
        }
    }).always(function () {
        if (onfinish) {
            onfinish();
        }
    });
};

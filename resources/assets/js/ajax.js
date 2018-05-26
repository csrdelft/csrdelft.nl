import $ from "jquery";
import {modalClose} from './modal';

/**
 * @see maalcie.js
 * @param {string} type
 * @param {string} url
 * @param {string} data
 * @param {jQuery} source
 * @param {function} onsuccess
 * @param {function|null} onerror
 * @param {function|null} onfinish
 */
export function ajaxRequest(type, url, data, source, onsuccess, onerror, onfinish = null) {
    if (source) {
        if (!source.hasClass('noanim')) {
            $(source).replaceWith(`<img id="${source.attr('id')}" title="${url}" src="/images/loading-arrows.gif" />`);
            source = $(`img[title="${url}"]`);
        }
        else if (source.hasClass('InlineForm')) {
            $(source).find('.FormElement:first').css({
                'background-image': 'url("/images/loading-fb.gif")',
                'background-repeat': 'no-repeat',
                'background-position': 'center right'
            });
        }
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
    }).done((data) => {
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
    }).fail((data, textStatus, errorThrown) => {
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
    }).always(() => {
        if (onfinish) {
            onfinish();
        }
    });
}

/**
 * @param url
 * @param ketzer
 * @returns {boolean}
 */
export function ketzerAjax(url, ketzer) {
    $(ketzer + ' .aanmelddata').html('Aangemeld:<br /><img src="/images/loading-arrows.gif" />');
    $.ajax({
        type: 'GET',
        cache: false,
        url: url,
        data: ''
    }).done((data) => {
        $(ketzer).replaceWith(data);
    }).fail((jqXHR, textStatus, errorThrown) => {
        $(ketzer + ' .aanmelddata').html('<span class="error">Error: </span>' + errorThrown);
        alert(errorThrown);
    });
    return true;
}
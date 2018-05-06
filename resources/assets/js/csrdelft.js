/**
 * csrdelft.nl javascript libje...
 */

import $ from 'jquery';
import Dropzone from 'dropzone/dist/dropzone-amd-module';

function preloadImg(href) {
    let img = $(document.createElement('img'));
    img[0].src = href;
    return img;
}

preloadImg('/images/loading-fb.gif');
preloadImg('/images/loading-arrows.gif');
preloadImg('/images/loading_bar_black.gif');

$(document).ready(function () {
    init_page();
});

function init_page() {
    zijbalk_scroll_fixed();
    init_dropzone();
    init_timeago_once();
    init_tooltip_once();
    init_sluit_meldingen();
    init_context($('body'));
    //init_geolocation();
}

/**
 * @see datatable.js
 * @see view/formulier/invoervelden/LidField.class.php
 * @param parent
 */
window.init_context = function(parent) {
    init_buttons(parent);
    init_forms(parent);
    init_timeago(parent);
    init_markitup(parent);
    init_tooltips(parent);
    init_hoverIntents(parent);
    init_lazy_images(parent);
};

function init_dropzone() {
    try {
        Dropzone.autoDiscover = false;
    }
    catch (err) {
        console.log(err);
        // Missing js file
    }
}

function init_timeago_once() {
    try {
        $.timeago.settings.strings = {
            prefiprefixAgo: '',
            prefixFromNow: 'sinds',
            suffixAgo: 'geleden',
            suffixFromNow: '',
            seconds: 'nog geen minuut',
            minute: '1 minuut',
            minutes: '%d minuten',
            hour: '1 uur',
            hours: '%d uur',
            day: '1 dag',
            days: '%d dagen',
            month: '1 maand',
            months: '%d maanden',
            year: '1 jaar',
            years: '%d jaar',
            wordSeparator: ' ',
            numbers: []
        };
    }
    catch (err) {
        console.log(err);
        // Missing js file
    }
}

function init_timeago(parent) {
    try {
        $(parent).find('abbr.timeago').timeago();
    }
    catch (err) {
        console.log(err);
        // Missing js file
    }
}

function init_tooltip_once() {
    try {
        // Change JQueryUI/tooltip plugin name to 'uitooltip' to fix name collision with Bootstrap/tooltip
        $.widget.bridge('uitooltip', $.ui.tooltip);
    }
    catch (err) {
        console.log(err);
        // Missing js file
    }
}

function init_tooltips(parent) {
    try {
        $(parent).uitooltip({
            track: true
        });
    }
    catch (err) {
        console.log(err);
        // Missing js file
    }
}

function init_markitup(parent) {
    try {
        $(parent).find('textarea.BBCodeField').markItUp(require('./bbcode-set'));
    }
    catch (err) {
        console.log(err);
        // Missing js file
    }
}

function init_geolocation() {

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

function init_lazy_images(parent) {
    $(parent).find('div.bb-img-loading').each(function () {
        let content = $(document.createElement('IMG'));
        content.on('error', function () {
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
            let foto = content.attr('src').indexOf('/plaetjes/fotoalbum/') >= 0;
            let video = $(this).parent().parent().hasClass('bb-video-preview');
            let hasAnchor = $(this).closest('a').length !== 0;
            $(this).parent().replaceWith($(this));
            if (!foto && !video && !hasAnchor) {
                $(this).wrap(`<a class="lightbox-link" href="${$(this).attr('src')}" data-lightbox="page-lightbox"></a>`);
            }
        });
    });
}

function init_sluit_meldingen() {
    $('#melding').on('click', '.alert', function () {
        $(this).slideUp(400, remove);
    });
}

function zijbalk_scroll_fixed() {
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
        modal_open(htmlString);
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
        modal_open(htmlString);
        return;
    }
    window.location.href = htmlString;
};

/**
 * @see bibliotheek.js
 * @param parent
 */
window.init_hoverIntents = function(parent) {
    $(parent).find('.hoverIntent').hoverIntent({
        over() {
            $(this).find('.hoverIntentContent').fadeIn();
        },
        out() {
            $(this).find('.hoverIntentContent').fadeOut();
        },
        timeout: 250
    });
};

function init_buttons(parent) {
    $(parent).find('.spoiler').bind('click.spoiler', function (event) {
        event.preventDefault();
        let button = $(this);
        let content = button.next('div.spoiler-content');
        if (button.html() === 'Toon verklapper') {
            button.html('Verberg verklapper');
        }
        else {
            button.html('Toon verklapper');
        }
        content.toggle(800, 'easeInOutCubic');
    });
    $(parent).find('.popup').bind('click.popup', modal_open);
    $(parent).find('.post').bind('click.post', knop_post);
    $(parent).find('.get').bind('click.get', knop_get);
    $(parent).find('.vergroot').bind('click.vergroot', function (event) {
        knop_vergroot($(this));
    });
}

function knop_vergroot(knop) {
    let id = knop.attr('data-vergroot'),
        oud = knop.attr('data-vergroot-oud');

    if (oud) {
        $(id).animate({'height': oud}, 600);
        knop.removeAttr('data-vergroot-oud');
        knop.find('span.fa').removeClass('fa-compress').addClass('fa-expand');
        knop.attr('title', 'Uitklappen');
    }
    else {
        knop.attr('title', 'Inklappen');
        knop.find('span.fa').removeClass('fa-expand').addClass('fa-compress');
        knop.attr('data-vergroot-oud', $(id).height());
        $(id).animate({
            'height': $(id).prop('scrollHeight') + 1
        }, 600);
    }
}

function knop_ajax(knop, type) {
    if (knop.hasClass('confirm') && !confirm(knop.attr('title') + '.\n\nWeet u het zeker?')) {
        modal_close();
        return false;
    }
    let source = knop,
        done = dom_update,
        data = knop.attr('data');

    if (knop.hasClass('popup')) {
        source = false;
    }
    if (knop.hasClass('prompt')) {
        data = data.split('=');
        let val = prompt(data[0], data[1]);
        if (!val) {
            return false;
        }
        data = encodeURIComponent(data[0]) + '=' + encodeURIComponent(val);
    }
    if (knop.hasClass('addfav')) {
        data = {
            'tekst': document.title.replace('C.S.R. Delft - ', ''),
            'link': this.location.href
        };
    }
    if (knop.hasClass('DataTableResponse')) {

        let tableId = knop.attr('data-tableid');
        if (!document.getElementById(tableId)) {
            tableId = knop.closest('form').attr('data-tableid');
            if (!document.getElementById(tableId)) {
                alert('DataTable not found');
            }
        }

        let selection = fnGetSelection('#' + tableId);
        data = {
            'DataTableId': tableId,
            'DataTableSelection[]': selection
        };

        done = function (response) {
            if (typeof response === 'object') { // JSON
                fnUpdateDataTable('#' + tableId, response);
                if (response.modal) {
                    modal_open(response.modal);
                    init_context($('#modal'));
                }
                else {
                    modal_close();
                }
            }
            else { // HTML
                dom_update(response);
            }
        };

        if (!knop.hasClass('SingleRow')) {
            source = false;
        }
    }
    if (knop.hasClass('ReloadPage')) {
        done = page_reload;
    }
    else if (knop.hasClass('redirect')) {
        done = page_redirect;
    }

    ajax_request(type, knop.attr('href'), data, source, done, alert);
}

/**
 * @see datatable.js
 * @param event
 * @returns {boolean}
 */
window.knop_post = function(event) {
    event.preventDefault();
    if ($(this).hasClass('range')) {
        if (event.target.tagName.toUpperCase() === 'INPUT') {
            taken_select_range(event);
        }
        else {
            taken_submit_range(event);
        }
        return false;
    }
    knop_ajax($(this), 'POST');
    return false;
};

function knop_get(event) {
    event.preventDefault();
    knop_ajax($(this), 'GET');
    return false;
}

function modal_open(htmlString) {
    if ($(this).hasClass('confirm') && !confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
        htmlString.preventDefault();
        return false;
    }

    let modal = $('#modal'),
        modalWrapper = $('#modal-wrapper');

    if (typeof htmlString === 'string' && htmlString !== '') {
        modal.html(htmlString);
        modal.find('input:visible:first').focus();
    }
    else {
        modalWrapper.modal('hide');
        modal.html('');
    }

    modalWrapper.modal();
}

function modal_close() {
    $('#modal-wrapper').modal('hide');
}

function init_forms(parent) {
    $(parent).find('form').submit(form_submit);
    $(parent).find('.submit').bind('click.submit', form_submit);
    $(parent).find('.reset').bind('click.reset', form_reset);
    $(parent).find('.cancel').bind('click.cancel', form_cancel);
    $(parent).find('.InlineFormToggle').bind('click.toggle', form_toggle);
    $(parent).find('.SubmitChange').bind('change.change', form_submit);
}

function form_ischanged(form) {
    let changed = false;
    $(form).find('.FormElement').not('.tt-hint').each(function () {
        let elmnt = $(this);
        if (elmnt.is('input:radio')) {
            if (elmnt.is(':checked') && elmnt.attr('origvalue') !== elmnt.val()) {
                changed = true;
                return false; // break each
            }
        }
        else if (elmnt.is('input:checkbox')) {
            if (elmnt.is(':checked') !== (elmnt.attr('origvalue') === '1')) {
                changed = true;
                return false; // break each
            }
        }
        else if (elmnt.val() !== elmnt.attr('origvalue')) {
            changed = true;
            return false; // break each
        }
    });
    return changed;
}

/**
 * @see templates/instellingen/beheer/instelling_row.tpl
 * @param form
 */
window.form_inline_toggle = form => {
    form.prev('.InlineFormToggle').toggle();
    form.toggle();
    form.children(':first').focus();
};

function form_toggle(event) {
    event.preventDefault();
    let form = $(this).next('form');
    form_inline_toggle(form);
    return false;
}

/**
 * @see view/formulier/invoervelden/InputField.abstract.php
 * @see view/formulier/invoervelden/ZoekField.php
 * @param event
 * @returns {boolean}
 */
window.form_submit = function(event) {
    if ($(this).hasClass('confirm')) {
        let q = $(this).attr('title');
        if (q) {
            q += '.\n\n';
        }
        else {
            q = 'Weet u het zeker?';
        }
        if (!confirm(q)) {
            event.preventDefault();
            return false;
        }
    }

    let form = $(this).closest('form');
    if (!form.hasClass('Formulier')) {
        if (event) {
            form = $(event.target.form);
        }
        else {
            return false;
        }
    }

    if (form.hasClass('PreventUnchanged') && !form_ischanged(form)) {
        event.preventDefault();
        alert('Geen wijzigingen');
        return false;
    }

    if ($(this).attr('href')) {
        form.attr('action', $(this).attr('href'));
    }

    if (form.hasClass('ModalForm') || form.hasClass('InlineForm')) {
        event.preventDefault();
        let formData = new FormData(form.get(0)),
            done = dom_update,
            source = false;

        if (form.hasClass('InlineForm')) {
            source = form;
            formData.append('InlineFormId', form.attr('id'));
            if (form.data('submitCallback')) {
                done = form.data('submitCallback');
            }
        }

        if (form.hasClass('DataTableResponse')) {

            let tableId = form.attr('data-tableid');
            if (!document.getElementById(tableId)) {
                alert('DataTable not found');
            }

            formData.append('DataTableId', tableId);
            let selection = fnGetSelection('#' + tableId);
            $.each(selection, function (key, value) {
                formData.append('DataTableSelection[]', value);
            });

            done = function (response) {
                if (typeof response === 'object') { // JSON
                    fnUpdateDataTable('#' + tableId, response);
                    if (response.modal) {
                        modal_open(response.modal);
                        init_context($('#modal'));
                    }
                    else {
                        modal_close();
                    }
                }
                else { // HTML
                    dom_update(response);
                }
            };

            if (!form.hasClass('noanim')) {
                source = false;
            }
        }

        if (form.hasClass('ReloadPage')) {
            done = page_reload;
        }
        else if (form.hasClass('redirect')) {
            done = page_redirect;
        }

        ajax_request('POST', form.attr('action'), formData, source, done, alert, function () {
            if (form.hasClass('SubmitReset')) {
                form_reset(event, form);
            }
        });

        return false;
    }
    form.unbind('submit');
    form.submit();
    return true;
};

function form_reset(event, form) {
    if (!form) {
        form = $(this).closest('form');
        event.preventDefault();
    }
    if ($(this).hasClass('confirm') && !confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
        return false;
    }
    form.find('.FormElement').each(function () {
        let orig = $(this).attr('origvalue');
        if (typeof orig === 'string') {
            $(this).val(orig);
        }
    });
    return false;
}

/**
 * @see view/formulier/invoervelden/InputField.abstract.php
 * @param event
 * @returns {boolean}
 */
window.form_cancel = function(event) {
    let source = $(event.target);
    if (source.length === 0) {
        source = $(this);
    }
    if (source.hasClass('confirm') && !confirm(source.attr('title') + '.\n\nWeet u het zeker?')) {
        event.preventDefault();
        return false;
    }
    let form = source.closest('form');
    if (form.hasClass('InlineForm')) {
        event.preventDefault();
        form_inline_toggle(form);
        return false;
    }
    if (source.hasClass('post')) {
        event.preventDefault();
        knop_post(event);
        return false;
    }
    if (form.hasClass('ModalForm')) {
        event.preventDefault();
        if (!form_ischanged(form) || confirm('Sluiten zonder wijzigingen op te slaan?')) {
            modal_close();
        }
        return false;
    }
    return true;
};

window.dom_update = function(htmlString) {
    htmlString = $.trim(htmlString);
    if (htmlString.substring(0, 9) === '<!DOCTYPE') {
        alert('response error');
        document.write(htmlString);
    }
    let html = $.parseHTML(htmlString, document, true);
    $(html).each(function () {
        let id = $(this).attr('id');
        if (id === 'modal') {
            modal_open();
        }
        else {
            modal_close();
        }
        let elmnt = $('#' + id);
        if (elmnt.length === 1) {
            if ($(this).hasClass('remove')) {
                elmnt.effect('fade', {}, 400, remove);
            }
            else {
                elmnt.replaceWith($(this).show()).effect('highlight');
            }
        }
        else {
            let parentid = $(this).attr('parentid');
            if (parentid) {
                $(this).prependTo(`#${parentid}`).show().effect('highlight');
            }
            else {
                $(this).prependTo('#maalcie-tabel tbody:visible:first').show().effect('highlight'); //FIXME: make generic
            }
        }
        init_context($(this));
        if (id === 'modal') {
            $('#modal-background').css('background-image', 'none');
            $('#modal').find('input:visible:first').focus();
        }
    });
};

/**
 * Wordt als callback gebruikt.
 */
function remove() {
    $(this).remove();
}

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
window.ajax_request = (type, url, data, source, onsuccess, onerror, onfinish) => {
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
        modal_open();
    }
    let contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
    let processData = true;
    if (data instanceof FormData) {
        contentType = false;
        processData = false;
    }
    let jqXHR = $.ajax({
        type: type,
        contentType: contentType,
        processData: processData,
        url: url,
        cache: false,
        data: data
    });
    jqXHR.done(function (data, textStatus, jqXHR) {
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
    });
    jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
        if (errorThrown === '') {
            errorThrown = 'Nog bezig met laden!';
        }
        if (source) {
            $(source).replaceWith('<img title="' + errorThrown + '" src="/plaetjes/famfamfam/cancel.png" />');
        }
        else {
            modal_close();
        }
        if (onerror) {
            onerror(jqXHR.responseText);
        }
    });
    jqXHR.always(function () {
        if (onfinish) {
            onfinish();
        }
    });
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

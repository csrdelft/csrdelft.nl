import $ from 'jquery';
import {knopGet, knopPost, knopVergroot} from './knop';
import {modalClose, modalOpen} from './modal';
import {formCancel, formReset, formSubmit, formToggle} from './formulier';

function initButtons(parent) {
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
    $(parent).find('.popup').bind('click.popup', modalOpen);
    $(parent).find('.post').bind('click.post', knopPost);
    $(parent).find('.get').bind('click.get', knopGet);
    $(parent).find('.vergroot').bind('click.vergroot', knopVergroot);
}

function initForms(parent) {
    $(parent).find('form').submit(formSubmit);
    $(parent).find('.submit').bind('click.submit', formSubmit);
    $(parent).find('.reset').bind('click.reset', formReset);
    $(parent).find('.cancel').bind('click.cancel', formCancel);
    $(parent).find('.InlineFormToggle').bind('click.toggle', formToggle);
    $(parent).find('.SubmitChange').bind('change.change', formSubmit);
}

function initTimeago(parent) {
    $(parent).find('abbr.timeago').timeago();
}

function initMarkitup(parent) {
    $(parent).find('textarea.BBCodeField').markItUp(require('./bbcode-set'));
}

function initTooltips(parent) {
    $(parent).uitooltip({track: true});
}

export function initHoverIntents(parent) {
    $(parent).find('.hoverIntent').hoverIntent({
        over() {
            $(this).find('.hoverIntentContent').fadeIn();
        },
        out() {
            $(this).find('.hoverIntentContent').fadeOut();
        },
        timeout: 250
    });
}

function initLazyImages(parent) {
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

export default function initContext(parent) {
    initButtons(parent);
    initForms(parent);
    initTimeago(parent);
    initMarkitup(parent);
    initTooltips(parent);
    initHoverIntents(parent);
    initLazyImages(parent);
}

export function domUpdate(htmlString) {
    htmlString = $.trim(htmlString);
    if (htmlString.substring(0, 9) === '<!DOCTYPE') {
        alert('response error');
        document.write(htmlString);
    }
    let html = $.parseHTML(htmlString, document, true);
    $(html).each(function () {
        let id = $(this).attr('id');

        let elmnt = $('#' + id);
        if (elmnt.length === 1) {
            if ($(this).hasClass('remove')) {
                elmnt.effect('fade', {}, 400, function() {
                    $(this).remove();
                });
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
        initContext($(this));

        if (id === 'modal') {
            modalOpen();
        }
        else {
            modalClose();
        }
    });
}

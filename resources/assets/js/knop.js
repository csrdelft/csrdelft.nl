import $ from 'jquery';

import {modalClose, modalOpen} from './modal';
import {ajaxRequest} from './ajax';
import initContext, {domUpdate} from './context';
import {takenSubmitRange, takenSelectRange} from './maalcie';
import {fnUpdateDataTable, fnGetSelection} from './datatable';
import {redirect, reload} from './util';

export function knopAjax(knop, type) {
    if (knop.hasClass('confirm') && !confirm(knop.attr('title') + '.\n\nWeet u het zeker?')) {
        modalClose();
        return false;
    }
    let source = knop,
        done = domUpdate,
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
                    modalOpen(response.modal);
                    initContext($('#modal'));
                }
                else {
                    modalClose();
                }
            }
            else { // HTML
                domUpdate(response);
            }
        };

        if (!knop.hasClass('SingleRow')) {
            source = false;
        }
    }
    if (knop.hasClass('ReloadPage')) {
        done = reload;
    }
    else if (knop.hasClass('redirect')) {
        done = redirect;
    }

    ajaxRequest(type, knop.attr('href'), data, source, done, alert);
}

/**
 * @see datatable.js
 * @param event
 * @returns {boolean}
 */
export function knopPost(event) {
    event.preventDefault();
    if ($(this).hasClass('range')) {
        if (event.target.tagName.toUpperCase() === 'INPUT') {
            takenSelectRange(event);
        }
        else {
            takenSubmitRange(event);
        }
        return false;
    }
    knopAjax($(this), 'POST');
    return false;
}

export function knopGet(event) {
    event.preventDefault();
    knopAjax($(this), 'GET');
    return false;
}

export function knopVergroot() {
    let knop = $(this),
        id = knop.attr('data-vergroot'),
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
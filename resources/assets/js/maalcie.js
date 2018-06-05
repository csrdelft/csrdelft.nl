/**
 * maalcie.js	|	P.W.G. Brussee (brussee@live.nl)
 *
 * requires jQuery & dragobject.js
 */
import $ from 'jquery';

import {ajaxRequest} from './ajax';
import {domUpdate} from './context';
import {dragObject} from './dragobject';

/**
 * @param {string} datum
 * @param {number} index
 */
function takenToggleDatumFirst(datum, index) {
    if ('taak-datum-head-' + datum === $('#maalcie-tabel tr:visible').eq(index).attr('id')) {
        $('#taak-datum-head-first').toggle();
    }
}

function takenColorDatum() {
    $('tr.taak-datum-summary:visible:odd th').css('background-color', '#FAFAFA');
    $('tr.taak-datum-summary:visible:even th').css('background-color', '#f5f5f5');
}

/**
 * @param {string} datum
 */
export function takenToggleDatum(datum) {
	takenToggleDatumFirst(datum, 0);
	$('.taak-datum-' + datum).toggle();
	takenToggleDatumFirst(datum, 1);
	takenColorDatum();

}


export function takenShowOld() {
	$('#taak-datum-head-first').show();
	$('tr.taak-datum-oud').show();
	takenColorDatum();
}

export function takenColorSuggesties() {
    let $suggestiesTabel = $('#suggesties-tabel');
    $suggestiesTabel.find('tr:visible:odd').css('background-color', '#FAFAFA');
    $suggestiesTabel.find('tr:visible:even').css('background-color', '#EBEBEB');
}

/**
 * @param {string} soort
 * @param {boolean} show
 */
export function takenToggleSuggestie(soort, show) {
	$('#suggesties-tabel .' + soort).each(function () {
        let verborgen = 0;
        if (typeof show !== 'undefined') {
			if (show) {
				$(this).removeClass(soort + 'verborgen');
			}
			else {
				$(this).addClass(soort + 'verborgen');
			}
		}
		else {
			$(this).toggleClass(soort + 'verborgen');
		}
		if ($(this).hasClass('geenvoorkeurverborgen')) {
			verborgen++;
		}
		if ($(this).hasClass('recentverborgen')) {
			verborgen++;
		}
		if ($(this).hasClass('jongsteverborgen')) {
			verborgen++;
		}
		if ($(this).hasClass('oudereverborgen')) {
			verborgen++;
		}
		if (verborgen > 0) {
			$(this).hide();
		}
		else {
			$(this).show();
		}
	});
	takenColorSuggesties();
}

let lastSelectedId;
/**
 * @param {KeyboardEvent} e
 */
export function takenSelectRange(e) {
	let withinRange = false;
	$('#maalcie-tabel').find('tbody tr td a input[name="' + $(e.target).attr('name') + '"]:visible').each(function () {
		let thisId = $(this).attr('id');
		if (thisId === lastSelectedId) {
			withinRange = !withinRange;
		}
		if (thisId === e.target.id) {
			withinRange = !withinRange;
			let check = $(this).prop('checked');
			setTimeout(function () { // workaround e.preventDefault()
				$('#' + thisId).prop('checked', check);
			}, 50);
		}
		else if (e.shiftKey && withinRange) {
			$(this).prop('checked', true);
		}
	});
	lastSelectedId = e.target.id;
}

/**
 * @param {Event} e
 * @returns {boolean}
 */
export function takenSubmitRange(e) {
	if (e.target.tagName.toUpperCase() === 'IMG') { // over an image inside of anchor
		e.target = $(e.target).parent();
	}
	$(e.target).find('input').prop('checked', true);
	if ($(e.target).hasClass('confirm') && !confirm($(e.target).attr('title') + '.\n\nWeet u het zeker?')) {
		return false;
	}
	$('input[name="' + $(e.target).find('input:first').attr('name') + '"]:visible').each(function () {
		if ($(this).prop('checked')) {
			ajaxRequest('POST', $(this).parent().attr('href'), $(this).parent().attr('post'), $(this).parent(), domUpdate, alert);
		}
	});
}

/* Ruilen van CorveeTaak */

/**
 * @param {Event} e
 */
function takenMagRuilen(e) {
	if (e.target.tagName.toUpperCase() === 'IMG') { // over an image inside of anchor
		e.target = $(e.target).parent();
	}

	let source= $('#' + dragObject.id);
	if ($(source).attr('id') !== $(e.target).attr('id')) {
		e.preventDefault();
	}
}

/**
 * @param {Event} e
 */
function takenRuilen(e) {
	e.preventDefault();
	let elmnt = e.target;
	if (elmnt.tagName.toUpperCase() === 'IMG') { // dropped on image inside of anchor
		elmnt = $(elmnt).parent();
	}
	let source = $('#' + dragObject.id);
	if (!confirm('Toegekende corveepunten worden meegeruild!\n\nDoorgaan met ruilen?')) {
		return;
	}
	let attr = $(source).attr('uid');
	if (typeof attr === 'undefined' || attr === false) {
		attr = '';
	}
	ajaxRequest('POST', $(elmnt).attr('href'), 'uid=' + attr, elmnt, domUpdate, alert);
	attr = $(elmnt).attr('uid');
	if (typeof attr === 'undefined' || attr === false) {
		attr = '';
	}
	ajaxRequest('POST', $(source).attr('href'), 'uid=' + attr, source, domUpdate, alert);
}

$(function () {
    $('a.ruilen').each(function () {
        $(this).removeClass('ruilen');
        $(this).on('dragover', takenMagRuilen);
        $(this).on('drop', takenRuilen);
    });
});

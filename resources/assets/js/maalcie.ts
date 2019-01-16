/**
 * maalcie.js	|	P.W.G. Brussee (brussee@live.nl)
 */
import $ from 'jquery';

import {ajaxRequest} from './ajax';
import {domUpdate} from './context';
import {dragObject} from './dragobject';

function takenToggleDatumFirst(datum:string, index:number) {
    if ('taak-datum-head-' + datum === $('#maalcie-tabel tr:visible').eq(index).attr('id')) {
        $('#taak-datum-head-first').toggle();
    }
}

function takenColorDatum() {
    $('tr.taak-datum-summary:visible:odd th').css('background-color', '#FAFAFA');
    $('tr.taak-datum-summary:visible:even th').css('background-color', '#f5f5f5');
}

export function takenToggleDatum(datum:string) {
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

export function takenToggleSuggestie(soort:string, show:boolean) {
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

let lastSelectedId :string;

export function takenSelectRange(e: KeyboardEvent) {
	let withinRange = false;
	$('#maalcie-tabel').find('tbody tr td a input[name="' + $(e.target!).attr('name') + '"]:visible').each(function () {
		let thisId = $(this).attr('id');
		if (thisId === lastSelectedId) {
			withinRange = !withinRange;
		}
		if (thisId === (e.target as Element).id) {
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
	lastSelectedId = (e.target as Element).id;
}

export function takenSubmitRange(e :Event) {
	let target = e.target as Element;
	if (target.tagName.toUpperCase() === 'IMG') { // over an image inside of anchor
		target = target.parentElement!;
	}
	$(target).find('input').prop('checked', true);
	if ($(target).hasClass('confirm') && !confirm($(target).attr('title') + '.\n\nWeet u het zeker?')) {
		return false;
	}
	$('input[name="' + $(target).find('input:first').attr('name') + '"]:visible').each(function () {
		if ($(this).prop('checked')) {
			ajaxRequest('POST', $(target).parent().attr('href')!, $(target).parent().attr('post')!, $(target).parent(), domUpdate, alert);
		}
	});
}

/* Ruilen van CorveeTaak */
function takenMagRuilen(e : Event) {
	let target = e.target as Element;
	if (target.tagName.toUpperCase() === 'IMG') { // over an image inside of anchor
		target = target.parentElement!;
	}

	let source= dragObject.el!;
	if (source.attr('id') !== target.id){
		e.preventDefault();
	}
}

function takenRuilen(e:Event) {
	e.preventDefault();
	let elmnt = e.target as Element;
	if (elmnt.tagName.toUpperCase() === 'IMG') { // dropped on image inside of anchor
		elmnt = elmnt.parentElement!;
	}
	let source = dragObject.el!;
	if (!confirm('Toegekende corveepunten worden meegeruild!\n\nDoorgaan met ruilen?')) {
		return;
	}
	let attr = source.attr('uid');
	if (!attr) {
		attr = '';
	}
	ajaxRequest('POST', elmnt.getAttribute('href')!, 'uid=' + attr, $(elmnt), domUpdate, alert);
	attr = $(elmnt).attr('uid');
	if (!attr) {
		attr = '';
	}
	ajaxRequest('POST', elmnt.getAttribute('href')!, 'uid=' + attr, source, domUpdate, alert);
}

$(function () {
    $('a.ruilen').each(function () {
        $(this).removeClass('ruilen');
        $(this).on('dragover', takenMagRuilen);
        $(this).on('drop', takenRuilen);
    });
});

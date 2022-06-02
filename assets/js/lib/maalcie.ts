import $ from 'jquery';
import { dragObject } from '../dragobject';
import { ajaxRequest } from './ajax';
import { domUpdate } from './domUpdate';
import { parents } from './dom';
import { throwError } from './util';

export function takenSubmitRange(e: Event): void | boolean {
	let target = e.target as HTMLElement;
	if (target.tagName.toUpperCase() === 'IMG') {
		// over an image inside of anchor
		target = parents(target);
	}
	$(target).find('input').prop('checked', true);
	if ($(target).hasClass('confirm') && !confirm($(target).attr('title') + '.\n\nWeet u het zeker?')) {
		return false;
	}
	$('input[name="' + $(target).find('input:first').attr('name') + '"]:visible').each(function () {
		if ($(this).prop('checked')) {
			const href = $(target).parent().attr('href');
			const post = $(target).parent().attr('post');

			if (!href || !post) {
				throw new Error('Element heeft geen href of post');
			}

			ajaxRequest('POST', href, post, target.parentElement, domUpdate, throwError);
		}
	});
}

export function takenColorSuggesties(): void {
	const $suggestiesTabel = $('#suggesties-tabel');
	$suggestiesTabel.find('tr:visible:odd').css('background-color', '#FAFAFA');
	$suggestiesTabel.find('tr:visible:even').css('background-color', '#EBEBEB');
}

export function takenToggleSuggestie(soort: string, show: boolean): void {
	$('#suggesties-tabel .' + soort).each(function () {
		let verborgen = 0;
		if (typeof show !== 'undefined') {
			if (show) {
				$(this).removeClass(soort + 'verborgen');
			} else {
				$(this).addClass(soort + 'verborgen');
			}
		} else {
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
		} else {
			$(this).show();
		}
	});
	takenColorSuggesties();
}

function takenToggleDatumFirst(datum: string, index: number) {
	if ('taak-datum-head-' + datum === $('#maalcie-tabel tr:visible').eq(index).attr('id')) {
		$('#taak-datum-head-first').toggleClass('verborgen');
	}
}

function takenColorDatum() {
	$('tr.taak-datum-summary:visible:odd th').css('background-color', '#FAFAFA');
	$('tr.taak-datum-summary:visible:even th').css('background-color', '#f5f5f5');
}

export function takenToggleDatum(datum: string): void {
	takenToggleDatumFirst(datum, 0);
	$('.taak-datum-' + datum).toggleClass('verborgen');
	takenToggleDatumFirst(datum, 1);
	takenColorDatum();
}

export function takenShowOld(): void {
	$('#taak-datum-head-first').removeClass('verborgen');
	$('tr.taak-datum-oud').removeClass('verborgen');
	takenColorDatum();
}

/* Ruilen van CorveeTaak */
export function takenMagRuilen(e: Event): void {
	let target = e.target as HTMLElement;
	if (target.tagName.toUpperCase() === 'IMG') {
		// over an image inside of anchor
		target = parents(target);
	}

	if (dragObject.el && dragObject.el.id !== target.id) {
		e.preventDefault();
	}
}

export function takenRuilen(e: Event): void {
	e.preventDefault();
	let elmnt = e.target as HTMLElement;
	if (elmnt.tagName.toUpperCase() === 'IMG') {
		// dropped on image inside of anchor
		elmnt = parents(elmnt);
	}
	const source = dragObject.el;
	if (!source || !confirm('Toegekende corveepunten worden meegeruild!\n\nDoorgaan met ruilen?')) {
		return;
	}
	let attr = source.getAttribute('uid');
	if (!attr) {
		attr = '';
	}
	const href = elmnt.getAttribute('href');
	if (!href) {
		throw new Error('Element heeft geen href');
	}
	ajaxRequest('POST', href, 'uid=' + attr, elmnt, domUpdate, throwError);
	attr = elmnt.getAttribute('uid');
	if (!attr) {
		attr = '';
	}
	ajaxRequest('POST', href, 'uid=' + attr, source, domUpdate, throwError);
}

let lastSelectedId: string;

export function takenSelectRange(e: KeyboardEvent): void {
	const target = e.target;

	if (!target) {
		throw new Error('Er is geen target');
	}

	let withinRange = false;
	$('#maalcie-tabel')
		.find('tbody tr td a input[name="' + $(target).attr('name') + '"]:visible')
		.each(function () {
			const thisId = $(this).attr('id');
			if (thisId === lastSelectedId) {
				withinRange = !withinRange;
			}
			if (thisId === (e.target as Element).id) {
				withinRange = !withinRange;
				const check = $(this).prop('checked');
				setTimeout(() => {
					// workaround e.preventDefault()
					$('#' + thisId).prop('checked', check);
				}, 50);
			} else if (e.shiftKey && withinRange) {
				$(this).prop('checked', true);
			}
		});
	lastSelectedId = (e.target as Element).id;
}

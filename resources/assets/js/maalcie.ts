/**
 * maalcie.js  |  P.W.G. Brussee (brussee@live.nl)
 */
import $ from 'jquery';
import {takenMagRuilen, takenRuilen} from './lib/maalcie';

let lastSelectedId: string;

export function takenSelectRange(e: KeyboardEvent) {
	let withinRange = false;
	$('#maalcie-tabel').find('tbody tr td a input[name="' + $(e.target!).attr('name') + '"]:visible').each(function () {
		const thisId = $(this).attr('id');
		if (thisId === lastSelectedId) {
			withinRange = !withinRange;
		}
		if (thisId === (e.target as Element).id) {
			withinRange = !withinRange;
			const check = $(this).prop('checked');
			setTimeout(() => { // workaround e.preventDefault()
				$('#' + thisId).prop('checked', check);
			}, 50);
		} else if (e.shiftKey && withinRange) {
			$(this).prop('checked', true);
		}
	});
	lastSelectedId = (e.target as Element).id;
}

$(() => {
	$('a.ruilen').each(function () {
		$(this).removeClass('ruilen');
		$(this).on('dragover', takenMagRuilen);
		$(this).on('drop', takenRuilen);
	});
});

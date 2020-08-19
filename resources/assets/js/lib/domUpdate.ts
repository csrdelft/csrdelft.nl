import $ from 'jquery';
import {init} from '../ctx';
import {modalClose, modalOpen} from './modal';
import {html, htmlParse} from './util';

export function domUpdate(this: HTMLElement | void, htmlString: string|null): void {
	if (typeof htmlString !== 'string') {
		return;
	}

	htmlString = $.trim(htmlString);
	if (htmlString.substring(0, 9) === '<!DOCTYPE') {
		alert('response error');
		throw new Error(htmlString)
	}
	const elements = htmlParse(htmlString);
	$(elements).each(function (index, element) {
		if (!(element instanceof Element)) {
			// element kan ook een stuk tekst zijn, hier kunnen we niets mee.
			return;
		}

		const $element = $(element);
		const id = $(element).attr('id');
		const parentId = $(element).attr('parentid');

		const target = $('#' + id);
		const targetParent = $('#' + parentId);
		if (target.length === 1) {
			if ($element.hasClass('remove')) {
				target.effect('fade', {}, 400, () => {
					target.remove();
				});
			} else {
				target.replaceWith($element.show().get()).effect('highlight');
			}
		} else if (targetParent.length === 1) {
			targetParent.append($element.show());
		} else if (element instanceof HTMLScriptElement) {
			$('head').append($element);
		} else {
			const parentid = $(this).attr('parentid');
			if (parentid) {
				$(this).prependTo(`#${parentid}`).show().effect('highlight');
			} else {
				$(this).prependTo('#maalcie-tabel tbody:visible:first').show().effect('highlight'); // FIXME: make generic
			}
		}
		init(element);

		if (id === 'modal') {
			modalOpen();
		} else {
			modalClose();
		}
	});
}

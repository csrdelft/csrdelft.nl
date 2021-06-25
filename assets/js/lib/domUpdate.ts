import $ from 'jquery'
import {init} from '../ctx';
import {modalClose, modalOpen} from './modal';
import {fadeAway, htmlParse} from './util';
import {select} from "./dom";

export function domUpdate(this: HTMLElement | void, htmlString: string | null): void {
	if (typeof htmlString !== 'string') {
		return;
	}

	htmlString = htmlString.trim();
	if (htmlString.substring(0, 9) === '<!DOCTYPE') {
		alert('response error');
		throw new Error(htmlString)
	}
	const elements = htmlParse(htmlString);
	for (const element of elements) {
		if (!(element instanceof HTMLElement)) {
			// element kan ook een stuk tekst zijn, hier kunnen we niets mee.
			continue;
		}

		const id = element.id;
		const parentId = element.getAttribute('parentid')

		if (!id && element instanceof HTMLScriptElement) {
			eval(element.innerText);
			modalClose()
			return;
		}

		// Probeer niet een lege id te laden.
		const target = id ? document.querySelector<HTMLElement>(`#${id}`) : null
		const targetParent = parentId ? document.querySelector<HTMLElement>(`#${parentId}`) : null

		if (target) {
			if (element.classList.contains('remove')) {
				fadeAway(target, 400)
			} else {
				// Jquery voert ook js uit, dat is nodig op dit moment voor forms
				$(target).replaceWith($(element))
			}
		} else if (targetParent) {
			targetParent.append(element);
		} else if (element instanceof HTMLScriptElement) {
			document.head.append(element)
		} else {
			select('#maalcie-tabel tbody:visible:first').append(element) // FIXME: make generic
		}
		init(element);

		if (id === 'modal') {
			modalOpen();
		} else {
			modalClose();
		}
	}
}

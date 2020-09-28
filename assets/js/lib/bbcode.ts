import axios from 'axios';
import $ from 'jquery';
import {Textarea, Textcomplete} from 'textcomplete';
import {init} from '../ctx';
import {html, preloadImage} from './util';

export const initBbPreviewBtn = (el: HTMLElement): void => {
	const previewId = el.dataset.bbpreviewBtn;

	if (!previewId) {
		throw new Error('Geen previewId gevonden')
	}

	const source = document.querySelector<HTMLTextAreaElement>('#' + previewId);
	const target = document.querySelector<HTMLElement>('#preview_' + previewId);

	if (!source || !target) {
		throw new Error('Bbpreview van niet bestaande elementen');
	}

	el.addEventListener('click', () => CsrBBPreviewEl(source, target));
};
export const initBbPreview = (el: HTMLTextAreaElement): void => {
	const previewId = el.dataset.bbpreview;

	if (!previewId) {
		throw new Error('Geen previewId gevonden')
	}

	const target = document.querySelector<HTMLElement>('#preview_' + previewId);

	if (!target) {
		throw new Error('Geen target gevonden voor bbpreview');
	}

	el.addEventListener('keyup', (event) => {
		if (event.key === 'Enter') { // enter
			CsrBBPreviewEl(el, target);
		}
	});
};

export const CsrBBPreviewEl = (source: HTMLTextAreaElement, target: HTMLElement, params: Record<string, string> = {}): void => {
	const bbcode = source.value;

	if (bbcode.trim() === '') {
		target.innerHTML = '';
		target.style.display = 'none';
		return;
	}

	axios.post('/tools/bbcode', {
		data: encodeURIComponent(bbcode),
		...params,
	}).then((response) => {
		target.innerHTML = response.data;
		init(target);
		target.style.display = 'block';
	}).catch((error) => {
		alert(error);
	});
};

export const loadBbImage = (el: HTMLElement): void => {
	const content = html`<img
													class="bb-img"
													alt="${el.getAttribute('title')}"
													style="${el.getAttribute('style')}"
													src="${el.getAttribute('src')}"/>`;
	content.onerror = () => {
		el.setAttribute('title', 'Afbeelding bestaat niet of is niet toegankelijk!');
		el.setAttribute('src', '/plaetjes/famfamafm/picture_error.png');
		el.style.width = '16px';
		el.style.height = '16px';
		el.classList.replace('bb-img-loading', 'bb-img');
	};

	const src = el.getAttribute('src')

	if (!src) {
		throw new Error('Bb image heeft geen src');
	}

	preloadImage(src, () => {
		const foto = src.indexOf('/plaetjes/fotoalbum/') >= 0;
		const video = el.parentElement?.parentElement?.classList.contains('bb-video-preview')
		const hasAnchor = $(el).closest('a').length !== 0;
		const parent = el.parentElement;

		if (!parent) {
			throw new Error("BBimage heeft geen parent.")
		}

		if (foto || video || hasAnchor) {
			parent.replaceChild(content, el);
		} else {
			const targetUrl = el.getAttribute('bb-href') == null ? el.getAttribute('src') : el.getAttribute('bb-href');
			const link = html`<a class="lightbox-link" href="${targetUrl}" data-lightbox="page-lightbox"></a>`;
			link.appendChild(content);
			parent.replaceChild(link, el);
		}
	});
};

export function activeerLidHints(textarea: HTMLElement): void {
	const editor = new Textarea(textarea);
	const textcomplete = new Textcomplete(editor);

	textcomplete.register([{
		// @...
		match: /(^|\s|])@((?:[^ ]+ ?){1,5})$/,
		replace(data: { label: string }) {
			return '$1[lid=' + data.label + ']';
		},
		search,
		template,
	}, {
		// [citaat=... of [lid=...
		index: 3,
		match: /(^|\s|])\[(citaat|lid)=(?:[0-9]{4}|([^\]]+))$/,
		search,
		template,
		replace(data: { label: string }) {
			return '$1[$2=' + data.label;
		},
	}]);
	textcomplete.on('rendered', () => {
		if (textcomplete.dropdown.items.length >= 1) {
			// Activeer eerste keuze standaard
			textcomplete.dropdown.items[0].activate();
		}
	});
}

function search(term: string, callback: (data: unknown) => void) {
	if (!term || term.length === 1) {
		callback([]);
	} else {
		$.ajax('/tools/naamsuggesties?vorm=user&zoekin=voorkeur&q=' + encodeURI(term))
			.done((data) => {
				callback(data);
			})
			.fail(() => {
				callback([]);
			});
	}
}

function template(data: { value: string }) {
	return data.value;
}

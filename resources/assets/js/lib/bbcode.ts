import axios from 'axios';
import $ from 'jquery';
// @ts-ignore
import {Textarea, Textcomplete} from 'textcomplete';
import {init} from '../ctx';
import {html, preloadImage} from './util';

export const initBbPreviewBtn = (el: HTMLElement) => {
	const previewId = el.dataset!.bbpreviewBtn!;
	const source = document.querySelector<HTMLTextAreaElement>('#' + previewId);
	const target = document.querySelector<HTMLElement>('#preview_' + previewId);

	if (!source || !target) {
		throw new Error('Bbpreview van niet bestaande elementen');
	}

	el.addEventListener('click', () => CsrBBPreviewEl(source, target));
};
export const initBbPreview = (el: HTMLTextAreaElement) => {
	const previewId = el.dataset!.bbpreview!;
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

export const CsrBBPreviewEl = (source: HTMLTextAreaElement, target: HTMLElement, params: object = {}) => {
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

export const loadBbImage = (el: HTMLElement) => {
	const content = html`<img
													class="bb-img"
													alt="${el.getAttribute('title')!}"
													style="${el.getAttribute('style')!}"
													src="${el.getAttribute('src')!}"/>`;
	content.onerror = () => {
		el.setAttribute('title', 'Afbeelding bestaat niet of is niet toegankelijk!');
		el.setAttribute('src', '/plaetjes/famfamafm/picture_error.png');
		el.style.width = '16px';
		el.style.height = '16px';
		el.classList.replace('bb-img-loading', 'bb-img');
	};

	preloadImage(el.getAttribute('src')!, () => {
		const foto = content.getAttribute('src')!.indexOf('/plaetjes/fotoalbum/') >= 0;
		const video = $(el).parent().parent().hasClass('bb-video-preview');
		const hasAnchor = $(el).closest('a').length !== 0;
		const parent = el.parentElement!;
		if (foto || video || hasAnchor) {
			parent.replaceChild(content, el);
		} else {
			const targetUrl = el.getAttribute('bb-href') == null ? el.getAttribute('src') : el.getAttribute('bb-href');
			const link = html`<a class="lightbox-link" href="${targetUrl!}" data-lightbox="page-lightbox"></a>`;
			link.appendChild(content);
			parent.replaceChild(link, el);
		}
	});
};

export function activeerLidHints(textarea: HTMLElement) {
	const editor = new Textarea(textarea);
	const textcomplete = new Textcomplete(editor);

	textcomplete.register([{
		// @...
		match: /(^|\s|])@((?:[^ ]+ ?){1,5})$/,
		replace(data: any) {
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
		replace(data: any) {
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

function search(term: string, callback: (data: any) => void) {
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

function template(data: any, term: string) {
	return data.value;
}

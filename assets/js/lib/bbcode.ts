import {html, preloadImage} from "./util";

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

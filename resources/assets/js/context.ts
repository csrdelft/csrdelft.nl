import $ from 'jquery';
import ctx, {init} from './ctx';
import {modalClose, modalOpen} from './modal';
import {html, htmlParse, preloadImage} from './util';

ctx.addHandler('div.bb-img-loading', (el: HTMLElement) => {
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
			const link = html`<a class="lightbox-link" href="${el.getAttribute('src')!}" data-lightbox="page-lightbox"></a>`;
			link.appendChild(content);
			parent.replaceChild(link, el);
		}
	});
});

export function domUpdate(this: HTMLElement | void, htmlString: string|object) {
	if (typeof htmlString !== 'string') {
		return;
	}

	htmlString = $.trim(htmlString);
	if (htmlString.substring(0, 9) === '<!DOCTYPE') {
		alert('response error');
		document.write(htmlString);
	}
	const elements = htmlParse(htmlString);
	$(elements).each(function (index, element) {
		if (!(element instanceof Element)) {
			// element kan ook een stuk tekst zijn, hier kunnen we niets mee.
			return;
		}

		const $element = $(element);
		const id = $(element).attr('id');

		const target = $('#' + id);
		if (target.length === 1) {
			if ($element.hasClass('remove')) {
				target.effect('fade', {}, 400, () => {
					target.remove();
				});
			} else {
				target.replaceWith($element.show().get()).effect('highlight');
			}
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

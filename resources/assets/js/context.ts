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
		if (!foto && !video && !hasAnchor) {
			const link = html`<a class="lightbox-link" href="${el.getAttribute('src')!}" data-lightbox="page-lightbox"></a>`;
			link.append(content);
			parent.replaceChild(el, link);
		} else {
			parent.replaceChild(el, content);
		}
	});
});

export function domUpdate(this: HTMLElement | void, htmlString: string) {
	htmlString = $.trim(htmlString);
	if (htmlString.substring(0, 9) === '<!DOCTYPE') {
		alert('response error');
		document.write(htmlString);
	}
	const elements = htmlParse(htmlString);
	$(elements).each(function () {
		const id = $(this).attr('id');

		const elmnt = $('#' + id);
		if (elmnt.length === 1) {
			if ($(this).hasClass('remove')) {
				elmnt.effect('fade', {}, 400, () => {
					$(this).remove();
				});
			} else {
				elmnt.replaceWith($(this).show()).effect('highlight');
			}
		} else {
			const parentid = $(this).attr('parentid');
			if (parentid) {
				$(this).prependTo(`#${parentid}`).show().effect('highlight');
			} else {
				$(this).prependTo('#maalcie-tabel tbody:visible:first').show().effect('highlight'); // FIXME: make generic
			}
		}
		init(this);

		if (id === 'modal') {
			modalOpen();
		} else {
			modalClose();
		}
	});
}

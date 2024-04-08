import $ from 'jquery';
import { modalClose } from './modal';
import axios, { AxiosError, Method } from 'axios';
import { select } from './dom';

export function ajaxRequest(
	type: Method,
	url: string,
	data:
		| string
		| FormData
		| Record<string, string | string[] | undefined>
		| null,
	source: Element | null,
	onsuccess: (data: unknown) => void,
	onerror?: (data: string) => void,
	onfinish?: () => void
): void {
	if (source) {
		if (!source.classList.contains('noanim')) {
			const img = $(
				`<i class="fas fa-spinner fa-spin" title="Laden: ${url}" aria-hidden="true"></i>`
			);
			$(source).replaceWith(img);
			source = img.get(0);
		} else if (source.classList.contains('InlineForm')) {
			try {
				Object.assign(select<HTMLElement>('.FormElement:first', source).style, <
					CSSStyleDeclaration
				>{
					backgroundImage: 'url("/images/loading-fb.gif")',
					backgroundPosition: 'center right',
					backgroundRepeat: 'no-repeat',
				});
			} catch (e) {
				// negeer
			}
		} else {
			source.classList.add('loading');
		}
	}
	axios(url, {
		method: type,
		data,
	})
		.then((response) => {
			if (source) {
				if (!$(source).hasClass('noanim')) {
					$(source).hide();
				} else if ($(source).hasClass('InlineForm')) {
					$(source).find('.FormElement:first').css({
						'background-image': '',
						'background-position': '',
						'background-repeat': '',
					});
				}
				source.classList.remove('loading');
			}
			onsuccess(response.data);
		})
		.catch((error: AxiosError) => {
			if (source) {
				$(source).replaceWith(
					'<img alt="Mislukt" title="' +
						// Dit was error.message, maar dan krijg je momenteel stek inception.
						"mislukt" +
						'" src="/plaetjes/famfamfam/cancel.png" />'
				);
			} else {
				modalClose();
			}
			if (onerror) {
				if (error.message.startsWith('<!DOC')) {
					onerror('Er ging iets fout, code is: ' + error.code);
				}
				onerror(error.message);
			}
		})
		.then(() => {
			if (onfinish) {
				onfinish();
			}
		});
}

/**
 * @param url
 * @param ketzer
 * @returns {boolean}
 */
export function ketzerAjax(url: string, ketzer: string): true {
	$(ketzer + ' .aanmeldbtn').addClass('loading');
	$.ajax({
		cache: false,
		data: '',
		type: 'GET',
		url,
	})
		.done((data) => {
			$(ketzer).replaceWith(data);
		})
		.fail((jqXHR, textStatus, errorThrown) => {
			$(ketzer + ' .aanmeldbtn').replaceWith(
				$(
					`<div class="alert alert-danger"><strong>Actie mislukt!</strong> ${errorThrown}</div>`
				)
			);
			throw new Error(jqXHR.responseText);
		});
	return true;
}

import $ from 'jquery';
import {modalClose} from './modal';

export function ajaxRequest(
	type: string,
	url: string,
	data: string | FormData | object,
	source: JQuery<Element> | false,
	onsuccess: (data: string) => void,
	onerror?: (data: string) => void,
	onfinish?: () => void) {
	if (source) {
		if (!source.hasClass('noanim')) {
			$(source).replaceWith(
				`<img alt="Laden" id="${source.attr('id')}" title="${url}" src="/images/loading-arrows.gif" />`);
			source = $(`img[title="${url}"]`);
		} else if (source.hasClass('InlineForm')) {
			$(source).find('.FormElement:first').css({
				'background-image': 'url("/images/loading-fb.gif")',
				'background-position': 'center right',
				'background-repeat': 'no-repeat',
			});
		} else {
			source.addClass('loading');
		}
	}
	let contentType: string | boolean = 'application/x-www-form-urlencoded; charset=UTF-8';
	let processData = true;
	if (data instanceof FormData) {
		contentType = false;
		processData = false;
	}
	$.ajax({
		cache: false,
		contentType,
		data,
		processData,
		type,
		url,
	}).done((response) => {
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
			source.removeClass('loading');
		}
		onsuccess(response);
	}).fail((response, textStatus, errorThrown) => {
		if (errorThrown === '') {
			errorThrown = 'Nog bezig met laden!';
		}
		if (source) {
			$(source).replaceWith('<img alt="Mislukt" title="' + errorThrown + '" src="/plaetjes/famfamfam/cancel.png" />');
		} else {
			modalClose();
		}
		if (onerror) {
			onerror(response.responseText);
		}
	}).always(() => {
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
export function ketzerAjax(url: string, ketzer: string) {
	$(ketzer + ' .aanmeldbtn').addClass('loading');
	$.ajax({
		cache: false,
		data: '',
		type: 'GET',
		url,
	}).done((data) => {
		$(ketzer).replaceWith(data);
	}).fail((jqXHR, textStatus, errorThrown) => {
		$(ketzer + ' .aanmeldbtn')
			.replaceWith($(`<div class="alert alert-danger"><strong>Actie mislukt!</strong> ${errorThrown}</div>`));
		alert(jqXHR.responseText);
	});
	return true;
}

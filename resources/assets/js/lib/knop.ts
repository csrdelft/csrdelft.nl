import $ from 'jquery';
import {fnGetSelection, fnUpdateDataTable, isDataTableResponse} from '../datatable/api';
import {ajaxRequest} from './ajax';

import {domUpdate} from './domUpdate';
import {takenSelectRange, takenSubmitRange} from './maalcie';
import {modalClose} from './modal';
import {redirect, reload} from './reload';

function knopAjax(knop: JQuery, type: string) {
	if (knop.hasClass('confirm') && !confirm(knop.attr('title') + '.\n\nWeet u het zeker?')) {
		modalClose();
		return false;
	}
	let source: JQuery | false = knop;
	let done = domUpdate;
	let data: undefined | string | Record<string, string | undefined | string[]> = knop.attr('data');

	if (knop.hasClass('popup')) {
		source = false;
	}
	if (knop.hasClass('prompt')) {
		if (!data) {
			throw new Error("Prompt knop heeft geen data")
		}
		const [key, value] = data.split('=')
		const userVal = prompt(key, value);
		if (!userVal) {
			return false;
		}
		data = encodeURIComponent(data[0]) + '=' + encodeURIComponent(userVal);
	}
	if (knop.hasClass('addfav')) {
		data = {
			tekst: document.title.replace('C.S.R. Delft - ', ''),
			link: window.location.href,
		};
	}
	if (knop.hasClass('DataTableRowKnop')) {
		const dataTableId = knop.parents('table').attr('id');
		data = {
			DataTableId: dataTableId,
			DataTableSelection: knop.parents('tr').attr('data-uuid'),
		};

		done = (response: unknown) => {
			if (isDataTableResponse(response)) { // JSON
				fnUpdateDataTable('#' + dataTableId, response);
				if (response && response.modal) {
					domUpdate(response.modal);
				} else {
					modalClose();
				}
			} else if (typeof response == "string") { // HTML
				domUpdate(response);
			}
		};
	}
	if (knop.hasClass('DataTableResponse')) {

		let tableId = knop.attr('data-tableid');
		if (!tableId || !document.getElementById(tableId)) {
			tableId = knop.closest('form').attr('data-tableid');
			if (!tableId || !document.getElementById(tableId)) {
				alert('DataTable not found');
				throw new Error("DataTable not found")
			}
		}

		data = {
			'DataTableId': tableId,
			'DataTableSelection[]': fnGetSelection('#' + tableId),
		};

		done = (response: unknown) => {
			if (isDataTableResponse(response)) { // JSON
				fnUpdateDataTable('#' + tableId, response);
				if (response.modal) {
					domUpdate(response.modal);
				} else {
					modalClose();
				}
			} else if (typeof response == 'string') { // HTML
				domUpdate(response);
			} else {
				throw new Error("Niets met deze response: " + response)
			}
		};

		if (!knop.hasClass('SingleRow')) {
			source = false;
		}
	}
	if (knop.hasClass('ReloadPage')) {
		done = reload;
	} else if (knop.hasClass('redirect')) {
		done = redirect;
	}

	const url = knop.attr('href');
	if (!url) {
		throw new Error("Knop heeft geen href")
	}
	ajaxRequest(type, url, data, source, done, alert);
}

export function knopPost(this: HTMLElement, event: Event): boolean {
	event.preventDefault();
	const target = event.target as HTMLElement;
	if ($(target).hasClass('range')) {
		if ((target).tagName.toUpperCase() === 'INPUT') {
			takenSelectRange(event as KeyboardEvent);
		} else {
			takenSubmitRange(event);
		}
		return false;
	}
	knopAjax($(this), 'POST');
	return false;
}

export function knopGet(this: HTMLElement, event: Event): false {
	event.preventDefault();
	knopAjax($(this), 'GET');
	return false;
}

export function knopVergroot(event: Event, el: HTMLElement): void {
	const target = el

	if (!(target instanceof HTMLElement)) {
		throw new Error("Knop vergroot klik heeft geen target")
	}

	const {vergroot, vergrootOud} = target.dataset

	if (!vergroot) {
		throw new Error("Knop vergroot heeft geen data-vergroot")
	}

	const icon = target.querySelector('span.fa')

	if (!icon) {
		throw new Error("knopVergroot heeft geen icon")
	}

	if (vergrootOud) {
		$(vergroot).animate({height: vergrootOud}, 600);

		delete target.dataset.vergrootOud

		icon.classList.remove('fa-compress', 'fa-expand')

		target.title = 'Uitklappen'
	} else {
		target.title = 'Inklappen'

		icon.classList.replace('fa-expand', 'fa-compress')

		target.dataset.vergrootOud = String($(vergroot).height())

		$(vergroot).animate({
			height: $(vergroot).prop('scrollHeight') + 1,
		}, 600);
	}
}

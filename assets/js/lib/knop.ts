import $ from 'jquery';
import { fnGetSelection, fnUpdateDataTable, isDataTableResponse } from '../datatable/api';
import { ajaxRequest } from './ajax';

import { domUpdate } from './domUpdate';
import { takenSelectRange, takenSubmitRange } from './maalcie';
import { modalClose } from './modal';
import { redirect, reload } from './reload';
import { parents, selectAll } from './dom';
import { throwError } from './util';
import { Method } from 'axios';

function knopAjax(knop: Element, type: Method) {
	if (!(knop instanceof HTMLElement)) {
		throw new Error('Knop is geen HTMLElement');
	}

	if (knop.classList.contains('confirm') && !confirm(knop.title + '.\n\nWeet u het zeker?')) {
		modalClose();
		return false;
	}
	let source: Element | null = knop;
	let done = domUpdate;
	let data: null | string | Record<string, string | undefined | string[]> = knop.getAttribute('data');

	if (knop.classList.contains('popup')) {
		source = null;
	}
	if (knop.classList.contains('prompt')) {
		if (!data) {
			throw new Error('Prompt knop heeft geen data');
		}
		const [key, value] = data.split('=');
		const userVal = prompt(key, value);
		if (!userVal) {
			return false;
		}
		data = encodeURIComponent(key) + '=' + encodeURIComponent(userVal);
	}
	if (knop.classList.contains('addfav')) {
		data = {
			tekst: document.title.replace('C.S.R. Delft - ', ''),
			link: window.location.href,
		};
	}
	if (knop.classList.contains('DataTableRowKnop')) {
		const dataTableId = parents(knop, 'table').id;
		data = {
			DataTableId: dataTableId,
			DataTableSelection: parents(knop, 'tr').dataset.uuid,
		};

		done = (response: unknown) => {
			if (isDataTableResponse(response)) {
				// JSON
				fnUpdateDataTable('#' + dataTableId, response);
				if (response && response.modal) {
					domUpdate(response.modal);
				} else {
					modalClose();
				}
			} else if (typeof response == 'string') {
				// HTML
				domUpdate(response);
			}
		};
	}
	if (knop.classList.contains('DataTableResponse')) {
		let tableId = knop.dataset.tableid;
		if (!tableId || !document.getElementById(tableId)) {
			const form = knop.closest('form');
			if (!form) {
				throw new Error('Geen form gevonden');
			}
			tableId = form.dataset.tableid;
			if (!tableId || !document.getElementById(tableId)) {
				throw new Error('DataTable not found');
			}
		}

		data = {
			DataTableId: tableId,
			DataTableSelection: fnGetSelection('#' + tableId),
		};

		done = (response: unknown) => {
			if (isDataTableResponse(response)) {
				// JSON
				fnUpdateDataTable('#' + tableId, response);
				if (response.modal) {
					domUpdate(response.modal);
				} else {
					modalClose();
				}
			} else if (typeof response == 'string') {
				// HTML
				domUpdate(response);
			} else {
				throw new Error('Niets met deze response: ' + response);
			}
		};

		if (!knop.classList.contains('SingleRow')) {
			source = null;
		}
	}
	if (knop.classList.contains('ReloadPage')) {
		done = reload;
	} else if (knop.classList.contains('redirect')) {
		done = redirect;
	}

	const url = knop.getAttribute('href');
	if (!url) {
		throw new Error('Knop heeft geen href');
	}
	ajaxRequest(type, url, data, source, done, throwError);
}

export function knopPost(el: HTMLElement, event: Event): boolean {
	event.preventDefault();
	const target = event.target as HTMLElement;
	if ($(target).hasClass('range')) {
		if (target.tagName.toUpperCase() === 'INPUT') {
			takenSelectRange(event as KeyboardEvent);
		} else {
			takenSubmitRange(event);
		}
		return false;
	}
	knopAjax(el, 'POST');
	return false;
}

export const initKnopPost = (el: HTMLElement): void => {
	el.classList.add('loaded');

	el.addEventListener('click', (ev) => knopPost(el, ev));
};

export const initKnopGet = (el: HTMLElement): void => {
	el.classList.add('loaded');

	el.addEventListener('click', (event) => {
		event.preventDefault();
		knopAjax(el, 'GET');
		return false;
	});
};

export const initKnopVergroot = (el: HTMLElement): void => el.addEventListener('click', (e) => knopVergroot(e, el));

export function knopVergroot(event: Event, el: Element): void {
	const target = el;

	if (!(target instanceof HTMLElement)) {
		throw new Error('Knop vergroot klik heeft geen target');
	}

	const { vergroot, vergrootOud } = target.dataset;

	if (!vergroot) {
		throw new Error('Knop vergroot heeft geen data-vergroot');
	}

	const icon = target.querySelector('span.fa');

	if (!icon) {
		throw new Error('knopVergroot heeft geen icon');
	}

	if (vergrootOud) {
		$(vergroot).animate({ height: vergrootOud }, 600);

		delete target.dataset.vergrootOud;

		icon.classList.replace('fa-compress', 'fa-expand');

		target.title = 'Uitklappen';
	} else {
		target.title = 'Inklappen';

		icon.classList.replace('fa-expand', 'fa-compress');

		target.dataset.vergrootOud = String($(vergroot).height());

		$(vergroot).animate(
			{
				height: $(vergroot).prop('scrollHeight') + 1,
			},
			600
		);
	}
}

export const initRadioButtons = (el: HTMLElement): void => {
	for (const btn of selectAll('a.btn', el)) {
		btn.addEventListener('click', () => {
			for (const active of selectAll('.active', el)) {
				active.classList.remove('active');
			}

			btn.classList.add('active');
		});
	}
};

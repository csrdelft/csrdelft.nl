import $ from 'jquery';
import {ajaxRequest} from './ajax';
import {domUpdate} from './context';
import ctx, {init} from './ctx';
import {fnGetSelection, fnUpdateDataTable} from './datatable/api';
import {takenSelectRange, takenSubmitRange} from './maalcie';

import {modalClose, modalOpen} from './modal';
import {redirect, reload} from './util';

ctx.addHandlers({
	'.get': (el) => el.addEventListener('click', knopGet),
	'.post': (el) => el.addEventListener('click', knopPost),
	'.vergroot': (el) => el.addEventListener('click', knopVergroot),
	'[data-buttons=radio]': (el) => {
		for (const btn of Array.from(el.querySelectorAll('a.btn'))) {
			btn.addEventListener('click',
				(event) => {
					for (const active of Array.from(el.querySelectorAll('.active'))) {
						active.classList.remove('active');
					}
					(event.target as Element).classList.add('active');
				},
			);
		}
	},
});

function knopAjax(knop: JQuery, type: string) {
	if (knop.hasClass('confirm') && !confirm(knop.attr('title') + '.\n\nWeet u het zeker?')) {
		modalClose();
		return false;
	}
	let source: JQuery|false = knop;
	let done = domUpdate;
	let data: string|string[]|object = knop.attr('data')!;

	if (knop.hasClass('popup')) {
		source = false;
	}
	if (knop.hasClass('prompt')) {
		data = data.split('=');
		const val = prompt(data[0], data[1]);
		if (!val) {
			return false;
		}
		data = encodeURIComponent(data[0]) + '=' + encodeURIComponent(val);
	}
	if (knop.hasClass('addfav')) {
		// @ts-ignore
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

		done = (response: any) => {
			if (typeof response === 'object') { // JSON
				fnUpdateDataTable('#' + dataTableId, response);
				if (response.modal) {
					modalOpen(response.modal);
					init(document.querySelector('#modal')!);
				} else {
					modalClose();
				}
			} else { // HTML
				domUpdate(response);
			}
		};
	}
	if (knop.hasClass('DataTableResponse')) {

		let tableId = knop.attr('data-tableid')!;
		if (!document.getElementById(tableId)) {
			tableId = knop.closest('form').attr('data-tableid')!;
			if (!document.getElementById(tableId)) {
				alert('DataTable not found');
			}
		}

		data = {
			'DataTableId': tableId,
			'DataTableSelection[]': fnGetSelection('#' + tableId),
		};

		done = (response: any) => {
			if (typeof response === 'object') { // JSON
				fnUpdateDataTable('#' + tableId, response);
				if (response.modal) {
					modalOpen(response.modal);
					init(document.querySelector('#modal')!);
				} else {
					modalClose();
				}
			} else { // HTML
				domUpdate(response);
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

	ajaxRequest(type, knop.attr('href')!, data, source, done, alert);
}

export function knopPost(this: HTMLElement, event: Event) {
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

function knopGet(this: HTMLElement, event: Event) {
	event.preventDefault();
	knopAjax($(this), 'GET');
	return false;
}

function knopVergroot(this: HTMLElement, event: Event) {
	const knop = $(this);
	const id = knop.attr('data-vergroot')!;
	const oud = knop.attr('data-vergroot-oud')!;

	if (oud) {
		$(id).animate({height: oud}, 600);
		knop.removeAttr('data-vergroot-oud');
		knop.find('span.fa').removeClass('fa-compress').addClass('fa-expand');
		knop.attr('title', 'Uitklappen');
	} else {
		knop.attr('title', 'Inklappen');
		knop.find('span.fa').removeClass('fa-expand').addClass('fa-compress');
		knop.attr('data-vergroot-oud', $(id).height()!);
		$(id).animate({
			height: $(id).prop('scrollHeight') + 1,
		}, 600);
	}
}

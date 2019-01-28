import $ from 'jquery';

import {modalClose, modalOpen} from './modal';
import {ajaxRequest} from './ajax';
import {domUpdate} from './context';
import {takenSelectRange, takenSubmitRange} from './maalcie';
import {fnGetSelection, fnUpdateDataTable} from './datatable/api';
import {redirect, reload} from './util';
import ctx, {init} from './ctx';

ctx.addHandlers({
	'.get': (el) => el.addEventListener('click.get', knopGet),
	'.post': (el) => el.addEventListener('click.post', knopPost),
	'.vergroot': (el) => el.addEventListener('click.vergroot', knopVergroot),
	'[data-buttons=radio]': (el) => {
		for (const btn of el.querySelectorAll('a.btn')) {
			btn.addEventListener('click',
				(event) => {
					for (const active of el.querySelectorAll('.active')) {
						active.classList.remove('active');
					}
					event.target.classList.add('active');
				}
			);
		}
	}
});

function knopAjax(knop, type) {
	if (knop.hasClass('confirm') && !confirm(knop.attr('title') + '.\n\nWeet u het zeker?')) {
		modalClose();
		return false;
	}
	let source = knop,
		done = domUpdate,
		data = knop.attr('data');

	if (knop.hasClass('popup')) {
		source = false;
	}
	if (knop.hasClass('prompt')) {
		data = data.split('=');
		let val = prompt(data[0], data[1]);
		if (!val) {
			return false;
		}
		data = encodeURIComponent(data[0]) + '=' + encodeURIComponent(val);
	}
	if (knop.hasClass('addfav')) {
		data = {
			'tekst': document.title.replace('C.S.R. Delft - ', ''),
			'link': window.location.href
		};
	}
	if (knop.hasClass('DataTableResponse')) {

		let tableId = knop.attr('data-tableid');
		if (!document.getElementById(tableId)) {
			tableId = knop.closest('form').attr('data-tableid');
			if (!document.getElementById(tableId)) {
				alert('DataTable not found');
			}
		}

		let selection = fnGetSelection('#' + tableId);
		data = {
			'DataTableId': tableId,
			'DataTableSelection[]': selection
		};

		done = function (response) {
			if (typeof response === 'object') { // JSON
				fnUpdateDataTable('#' + tableId, response);
				if (response.modal) {
					modalOpen(response.modal);
					init(document.querySelector('#modal'));
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

	ajaxRequest(type, knop.attr('href'), data, source, done, alert);
}

export function knopPost(event) {
	event.preventDefault();
	if ($(this).hasClass('range')) {
		if (event.target.tagName.toUpperCase() === 'INPUT') {
			takenSelectRange(event);
		} else {
			takenSubmitRange(event);
		}
		return false;
	}
	knopAjax($(this), 'POST');
	return false;
}

function knopGet(event) {
	event.preventDefault();
	knopAjax($(this), 'GET');
	return false;
}

function knopVergroot() {
	let knop = $(this),
		id = knop.attr('data-vergroot'),
		oud = knop.attr('data-vergroot-oud');

	if (oud) {
		$(id).animate({'height': oud}, 600);
		knop.removeAttr('data-vergroot-oud');
		knop.find('span.fa').removeClass('fa-compress').addClass('fa-expand');
		knop.attr('title', 'Uitklappen');
	} else {
		knop.attr('title', 'Inklappen');
		knop.find('span.fa').removeClass('fa-expand').addClass('fa-compress');
		knop.attr('data-vergroot-oud', $(id).height());
		$(id).animate({
			'height': $(id).prop('scrollHeight') + 1
		}, 600);
	}
}

import $ from 'jquery';
import {ajaxRequest} from './ajax';
import initContext, {domUpdate} from './context';
import {DatatableResponse, fnGetSelection, fnUpdateDataTable} from './datatable/api';

import {modalClose, modalOpen} from './modal';

import {redirect, reload} from './util';
import SubmitEvent = JQuery.SubmitEvent;

export function formIsChanged(form: JQuery<EventTarget>) {
	let changed = false;
	$(form).find('.FormElement').not('.tt-hint').each(function () {
		const elmnt = $(this);
		if (elmnt.is('input:radio')) {
			if (elmnt.is(':checked') && elmnt.attr('origvalue') !== elmnt.val()) {
				changed = true;
				return false; // break each
			}
		} else if (elmnt.is('input:checkbox')) {
			if (elmnt.is(':checked') !== (elmnt.attr('origvalue') === '1')) {
				changed = true;
				return false; // break each
			}
		} else if (elmnt.val() !== elmnt.attr('origvalue')) {
			changed = true;
			return false; // break each
		}
	});
	return changed;
}

/**
 * @see templates/instellingen/beheer/instelling_row.tpl
 * @param form
 */
export function formInlineToggle(form: JQuery<EventTarget>) {
	form.prev('.InlineFormToggle').toggle();
	form.toggle();
	form.children(':first').trigger('focus');
}

export function formToggle(this: JQuery, event: Event) {
	event.preventDefault();
	const form = $(this).next('form');
	formInlineToggle(form);
	return false;
}

export function formReset(event: SubmitEvent, form: JQuery<EventTarget>) {
	if (!form) {
		form = $(event.target!).closest('form');
		event.preventDefault();
	}
	if ($(event.target!).hasClass('confirm') && !confirm($(event.target!).attr('title') + '.\n\nWeet u het zeker?')) {
		return false;
	}
	form.find('.FormElement').each(function () {
		const orig = $(event.target!).attr('origvalue');
		if (typeof orig === 'string') {
			$(this).val(orig);
		}
	});
	return false;
}

/**
 * @see view/formulier/invoervelden/InputField.abstract.php
 * @see view/formulier/invoervelden/ZoekField.php
 * @param event
 * @returns {boolean}
 */
export function formSubmit(this: JQuery, event: SubmitEvent) {
	if ($(this).hasClass('confirm')) {
		let q = $(this).attr('title');
		if (q) {
			q += '.\n\n';
		} else {
			q = 'Weet u het zeker?';
		}
		if (!confirm(q)) {
			event.preventDefault();
			return false;
		}
	}

	let form = $(this).closest('form');
	if (!form.hasClass('Formulier')) {
		if (event) {
			form = $(event.target.form);
		} else {
			return false;
		}
	}

	if (form.hasClass('PreventUnchanged') && !formIsChanged(form)) {
		event.preventDefault();
		alert('Geen wijzigingen');
		return false;
	}

	if ($(this).attr('href')) {
		form.attr('action', $(this).attr('href')!);
	}

	if (!(form.hasClass('ModalForm') || form.hasClass('InlineForm'))) {
		form.off('submit');
		form.trigger('submit');
		return true;
	} else {
		event.preventDefault();
		const formData = new FormData(form.get(0) as HTMLFormElement);
		let done = domUpdate;
		let source: JQuery | boolean = false;

		if (form.hasClass('InlineForm')) {
			source = form;
			formData.append('InlineFormId', form.attr('id')!);
			if (form.data('submitCallback')) {
				done = form.data('submitCallback');
			}
		}

		if (form.hasClass('DataTableResponse')) {

			const tableId = form.attr('data-tableid')!;
			if (!document.getElementById(tableId)) {
				alert('DataTable not found');
			}

			formData.append('DataTableId', tableId);
			const selection = fnGetSelection('#' + tableId);
			$.each(selection, (key, value) => {
				formData.append('DataTableSelection[]', value);
			});

			done = (response: DatatableResponse | string) => {
				if (typeof response === 'object') { // JSON
					fnUpdateDataTable('#' + tableId, response);
					if (response.modal) {
						modalOpen(response.modal);
						initContext($('#modal'));
					} else {
						modalClose();
					}
				} else { // HTML
					domUpdate(response);
				}
			};

			if (!form.hasClass('noanim')) {
				source = false;
			}
		}

		if (form.hasClass('ReloadPage')) {
			done = reload;
		} else if (form.hasClass('redirect')) {
			done = redirect;
		}

		ajaxRequest('POST', form.attr('action')!, formData, source, done, alert, () => {
			if (form.hasClass('SubmitReset')) {
				formReset(event, form);
			}
		});

		return false;
	}
}

/**
 * @see view/formulier/invoervelden/InputField.abstract.php
 * @param event
 * @returns {boolean}
 */
export function formCancel(event: Event) {
	const source = $(event.target!);
	if (source.hasClass('confirm') && !confirm(source.attr('title') + '.\n\nWeet u het zeker?')) {
		event.preventDefault();
		return false;
	}
	const form = source.closest('form')!;
	if (form.hasClass('InlineForm')) {
		event.preventDefault();
		formInlineToggle(form);
		return false;
	}
	if (form.hasClass('ModalForm')) {
		event.preventDefault();
		if (!formIsChanged(form) || confirm('Sluiten zonder wijzigingen op te slaan?')) {
			modalClose();
		}
		return false;
	}
	return true;
}

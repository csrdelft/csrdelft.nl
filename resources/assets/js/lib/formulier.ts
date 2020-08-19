import axios from 'axios';
import $ from 'jquery';
import { fnGetSelection, fnUpdateDataTable, isDataTableResponse} from '../datatable/api';
import {ajaxRequest} from './ajax';
import {domUpdate} from './domUpdate';
import {modalClose} from './modal';
import {redirect, reload} from './reload';

export function formIsChanged(form: JQuery<EventTarget>): boolean {
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
export function formInlineToggle(form: JQuery<EventTarget>): void {
	form.prev('.InlineFormToggle').toggle();
	form.toggle();
	form.children(':first').trigger('focus');
}

export function formToggle(target: Element, event: Event): false {
	event.preventDefault();
	const form = $(target).next('form');
	formInlineToggle(form);
	return false;
}

export function formReset(event: Event, form?: JQuery<unknown>): false {
	const target = event.target

	if (!target) {
		throw new Error("formReset: geen EventTarget")
	}

	if (!form) {
		form = $(target).closest('form');
		event.preventDefault();
	}

	if ($(target).hasClass('confirm') && !confirm($(target).attr('title') + '.\n\nWeet u het zeker?')) {
		return false;
	}

	form.find('.FormElement').each(function () {
		const orig = $(target).attr('origvalue');
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
export function formSubmit(event: Event): boolean {
	const target = event.target as Element;
	const $target = $(target);
	if ($target.hasClass('confirm')) {
		let q = $target.attr('title');
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

	const form = $target.closest('form');
	if (!form.hasClass('Formulier')) {
		return false;
	}

	if (form.hasClass('PreventUnchanged') && !formIsChanged(form)) {
		event.preventDefault();
		alert('Geen wijzigingen');
		return false;
	}

	const href = $target.attr('href');
	if (href) {
		form.attr('action', href);
	}

	if (!(form.hasClass('ModalForm') || form.hasClass('InlineForm'))) {
		form.off('submit');
		form.trigger('submit');
		return true;
	} else {
		event.preventDefault();
		const formData = new FormData(form.get(0) as HTMLFormElement);
		let done = domUpdate;
		let source: JQuery<Element> | boolean = false;

		if (form.hasClass('InlineForm')) {
			source = form;
			const id = form.attr('id')
			if (id) {
				formData.append('InlineFormId', id);
			}
			if (form.data('submitCallback')) {
				done = form.data('submitCallback');
			}
		}

		if (form.hasClass('ModalForm')) {
			done = (response: unknown) => {
				if (typeof response === 'string') {
					domUpdate(response);
				} else {
					modalClose();
				}
			};
		}

		if (form.hasClass('DataTableResponse')) {

			const tableId = form.attr('data-tableid');
			if (!tableId || !document.getElementById(tableId)) {
				throw new Error("DataTable not found")
			}

			formData.append('DataTableId', tableId);
			const selection = fnGetSelection('#' + tableId);
			$.each(selection, (key, value) => {
				formData.append('DataTableSelection[]', value);
			});

			done = (response: unknown) => {
				if (isDataTableResponse(response)) { // JSON
					fnUpdateDataTable('#' + tableId, response);
					if (response.modal) {
						domUpdate(response.modal);
					} else {
						modalClose();
					}
				} else if (typeof response === 'string') { // HTML
					domUpdate(response);
				} else {
					throw new Error('onbekende response' + response)
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

		const url = form.attr('action')!;

		if (!url) {
			throw new Error("Form heeft geen action")
		}

		ajaxRequest('POST', url, formData, source, done, alert, () => {
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
export function formCancel(event: Event): boolean {
	const sourceEl = event.target

	if (!sourceEl || !(sourceEl instanceof HTMLElement)) {
		throw new Error("formCancel: Geen EventTarget")
	}

	const source = $(sourceEl);
	if (sourceEl.classList.contains('confirm') && !confirm(sourceEl.title + '.\n\nWeet u het zeker?')) {
		event.preventDefault();
		return false;
	}
	const form = source.closest('form');

	if (form.hasClass('InlineForm')) {
		event.preventDefault();
		formInlineToggle(form);
		return false;
	}
	if (form.hasClass('ModalForm')) {
		event.preventDefault();
		const href = source.attr('href');
		if (href) {
			axios.get(href);
		}
		if (!formIsChanged(form) || confirm('Sluiten zonder wijzigingen op te slaan?')) {
			modalClose();
		}
		return false;
	}
	return true;
}

export function insertPlaatje(id: string): void {
	$.markItUp({replaceWith: '[plaatje]' + id + '[/plaatje]'});
}

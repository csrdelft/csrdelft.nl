import axios from 'axios';
import $ from 'jquery';
import {fnGetSelection, fnUpdateDataTable, isDataTableResponse} from '../datatable/api';
import {ajaxRequest} from './ajax';
import {domUpdate} from './domUpdate';
import {modalClose} from './modal';
import {redirect, reload} from './reload';
import {parents, select, selectAll} from "./dom";
import {throwError} from "./util";

require('../editor')

export function formIsChanged(form: HTMLFormElement): boolean {
	let changed = false;
	selectAll<HTMLInputElement>('.FormElement:not(.tt-hint)', form).forEach(el => {
		const origValue = el.getAttribute('origvalue');

		if (el.type == 'radio') {
			if (el.checked && origValue !== el.value) {
				changed = true
			}
		} else
		if (el.type == 'checkbox') {
			if (Boolean(origValue) !== el.checked) {
				changed = true
			}
		} else
		if (el.value !== origValue) {
			changed = true
		}
	})

	return changed;
}

/**
 * @see templates/instellingen/beheer/instelling_row.tpl
 * @param form
 */
export function formInlineToggle(form: HTMLElement): void {
	const $form = $(form)
	$form.prev('.InlineFormToggle').toggle();
	$form.toggle();
	$form.children(':first').trigger('focus');
}

export function formToggle(target: HTMLElement, event: Event): false {
	event.preventDefault();

	formInlineToggle(select('form', parents(target)));

	return false;
}

export function formReset(event: Event, form?: HTMLFormElement | null): false {
	const target = event.target

	if (!target || !(target instanceof HTMLElement)) {
		throw new Error("formReset: geen EventTarget")
	}

	if (!form) {
		form = target.closest('form');
		event.preventDefault();
	}

	if (!form) {
		throw new Error("Geen form gevonden in formReset")
	}

	if (target.classList.contains('confirm') && !confirm(target.title + '.\n\nWeet u het zeker?')) {
		return false;
	}

	selectAll('.FormElement', form).forEach(function (el) {
		const orig = el.getAttribute('origvalue')
		if (orig && (el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement)) {
			el.value = orig
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
	const target = event.target as HTMLElement;
	if (target.classList.contains('confirm')) {
		let q = target.title;
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

	const form = target.closest('form');
	if (!form || !form.classList.contains('Formulier')) {
		return false;
	}

	if (form.classList.contains('PreventUnchanged') && !formIsChanged(form)) {
		event.preventDefault();
		alert('Geen wijzigingen');
		return false;
	}

	const href = target.getAttribute('href');
	if (href && href != '#') {
		form.setAttribute('action', href);
	}

	if (!(form.classList.contains('ModalForm') || form.classList.contains('InlineForm'))) {
		// kijk of er een manier is die niet jquery is
		$(form).off('submit');
		$(form).trigger('submit');
		return true;
	} else {
		event.preventDefault();
		const formData = new FormData(form);
		let done = domUpdate;
		let source: Element | null = null

		if (form.classList.contains('InlineForm')) {
			source = form;
			const id = form.id
			if (id) {
				formData.append('InlineFormId', id);
			}
			if ($(form).data('submitCallback')) {
				done = $(form).data('submitCallback');
			}
		}

		if (form.classList.contains('ModalForm')) {
			done = (response: unknown) => {
				if (typeof response === 'string') {
					domUpdate(response);
				} else {
					modalClose();
				}
			};
		}

		if (form.classList.contains('DataTableResponse')) {

			const tableId = form.dataset.tableid
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

			if (!form.classList.contains('noanim')) {
				source = null;
			}
		}

		if (form.classList.contains('ReloadPage')) {
			done = reload;
		} else if (form.classList.contains('redirect')) {
			done = redirect;
		}

		const url = form.getAttribute('action');

		if (!url) {
			throw new Error("Form heeft geen action")
		}

		ajaxRequest('POST', url, formData, source, done, throwError, () => {
			if (form.classList.contains('SubmitReset')) {
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

	if (sourceEl.classList.contains('confirm') && !confirm(sourceEl.title + '.\n\nWeet u het zeker?')) {
		event.preventDefault();
		return false;
	}
	const form = sourceEl.closest('form');

	if (!form) {
		throw new Error("Geen form in formCancel")
	}

	if (form.classList.contains('InlineForm')) {
		event.preventDefault();
		formInlineToggle(form);
		return false;
	}
	if (form.classList.contains('ModalForm')) {
		event.preventDefault();
		const href = sourceEl.getAttribute('href')
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

export function initSterrenField(el: HTMLElement): void {
	$(el).raty({
		...JSON.parse(el.dataset.config),
		path: '/images/raty/',
		cancelHint: 'Wis beoordeling',
		cancelPlace: 'right',
		noRatedMsg: '',
		click: function (score) {
			$(this).raty('score', score)
			$(this).closest('form').submit()
		}
	})
}

export function initFileField(el: HTMLInputElement): void {
	const maxSize = Number(el.dataset.maxSize)
	const maxSizeReadable = el.dataset.maxSizeReadable

	el.addEventListener('change', () => {
		if (!el.files) {
			return
		}

		for (const file of Array.from(el.files)) {
			if (file.size > maxSize) {

				alert(file.name + ' is te groot: Maximaal ' + maxSizeReadable + '\n\nSplits het bestand op of gebruik een andere upload-methode.');

				el.value = '';
			}
		}
	})
}

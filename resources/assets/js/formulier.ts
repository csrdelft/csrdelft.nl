import Inputmask from 'inputmask';
import $ from 'jquery';
import {ajaxRequest} from './ajax';
import {bbCodeSet} from './bbcode-set';
import {domUpdate} from './context';
import ctx, {init} from './ctx';
import {DatatableResponse, fnGetSelection, fnUpdateDataTable} from './datatable/api';

import {modalClose, modalOpen} from './modal';

import {redirect, reload} from './util';

ctx.addHandlers({
	'.InlineFormToggle': (el) => el.addEventListener('click', (event) => formToggle(el, event)),
	'.SubmitChange': (el) => el.addEventListener('change', formSubmit),
	'.cancel': (el) => el.addEventListener('click', formCancel),
	'.reset': (el) => el.addEventListener('click', formReset),
	// '.submit': (el) => el.addEventListener('click', formSubmit),
	// 'form.Formulier': (el) => $(el).on('submit', formSubmit), // dit is sterker dan addEventListener
	'textarea.BBCodeField': (el) => $(el).markItUp(bbCodeSet),
	'time.timeago': (el) => $(el).timeago(),
	'[data-sum]': initSum,
	'input': (el) => Inputmask().mask(el),
});

Inputmask.extendAliases({
	bedrag: {
		prefix: '€ ',
		removeMaskOnSubmit: true,
		autoUnmask: true,
		unmaskAsNumber: true,
		groupSeparator: '.',
		radixPoint: ',',
		alias: 'numeric',
		placeholder: '0',
		autoGroup: true,
		digits: 2,
		digitsOptional: false,
		clearMaskOnLostFocus: false,
		onBeforeMask: (initialValue: string) => String(Number(initialValue) / 100).replace('.', ','),
		onUnMask: (maskedValue: string, unmaskedValue: string) => Number(unmaskedValue.replace(',', '').replace('.', '')),
	},
})
;

function initSum(el: Element) {
	const element = el as HTMLInputElement;
	const target = element.dataset.sum!.split('*');
	const watch = document.querySelectorAll(`[name*=${target[0]}][name*=${target[1]}]`);
	const values: number[] = [];

	for (let i = 0; i < watch.length; i++) {
		watch[i].addEventListener('keyup', (event) => {
			const eventTarget = event.target as HTMLElement;
			values[i] = Number((event.target as HTMLInputElement).value);

			element.value = String(values.reduce((a, b) => a + b));
		});
	}
}

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

export function formToggle(target: Element, event: Event) {
	event.preventDefault();
	const form = $(target).next('form');
	formInlineToggle(form);
	return false;
}

export function formReset(event: Event, form?: JQuery<any>) {
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
export function formSubmit(event: Event) {
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

	if ($target.attr('href')) {
		form.attr('action', $target.attr('href')!);
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
			formData.append('InlineFormId', form.attr('id')!);
			if (form.data('submitCallback')) {
				done = form.data('submitCallback');
			}
		}

		if (form.hasClass('ModalForm')) {
			done = (response: any) => {
				if (typeof response === 'string') {
					domUpdate(response);
				} else {
					modalClose();
				}
			};
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
						init(document.querySelector('#modal')!);
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

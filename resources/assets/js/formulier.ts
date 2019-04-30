import $ from 'jquery';
import maskInput from 'vanilla-text-mask';
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
	'.submit': (el) => el.addEventListener('click', formSubmit),
	'form.Formulier': (el) => $(el).on('submit', formSubmit), // dit is sterker dan addEventListener
	'textarea.BBCodeField': (el) => $(el).markItUp(bbCodeSet),
	'time.timeago': (el) => $(el).timeago(),
	'[data-sum]': initSum,
	'input[data-mask=bedrag]': (el) => {
		maskInput({
			inputElement: el,
			placeholderChar: '0',
			// mask: (rawValue: string) => ['€', /\d+/, /\d/, ',', /\d/, /\d/],
			// guide: false,
			mask: (rawValue: string) => {
				console.log(rawValue);

				if (rawValue.length === 0 || rawValue[0].match(/\d/)) {
					return ['€', /\d/, ',', /\d/, /\d/];
				}

				rawValue = rawValue.replace('.', ',');

				const sepPosition = rawValue.indexOf(',');

				let beforeSep = 0;

				if (sepPosition === -1) {
					// const matches = rawValue.match(/\d/g);
					// console.log(matches);
					// beforeSep = matches != null ? matches.length : 1;
				} else {
					beforeSep = sepPosition === -1 ? (rawValue.length === 2) ? 3 : rawValue.length : Math.max(2, sepPosition - 1);
				}
				const mask: Array<string | RegExp> = ['€'];

				for (let i = 0; i < beforeSep; i++) {
					mask.push(/\d/);
				}

				mask.push(',', /\d/, /\d/);
				return mask;
			},
			showMask: true,
			// pipe: (str: string) => ({value: str.replace('.', ','), indexOfPipedChars: [str.length]}),
			// mask: createNumberMask({
			// 	prefix: '€',
			// 	decimalSymbol: ',',
			// 	allowDecimal: true,
			// 	requireDecimal: true,
			// 	fixedDecimalScale: true,
			// }),
			// pipe(str: string, config: any) {
			// 	console.log(str);
			// 	if (str === ' €_') {
			// 		return {value: '€0,00', indexOfPipedChars: [0, 1, 2, 3, 4, 5]};
			// 	}
			// 	let value = str;
			// 	let indexOfPipedChars: number[] = [];
			// 	if (str.indexOf(',') === -1) {
			// 		value = ',00';
			// 		indexOfPipedChars = [str.length + 1, str.length + 2, str.length + 3];
			// 	} else if (str.indexOf(',') > str.length - 1) {
			// 		value = '00';
			// 		indexOfPipedChars = [str.length + 1, str.length + 2];
			// 	} else if (str.indexOf(',') > str.length - 2) {
			// 		value = '0';
			// 		indexOfPipedChars = [str.length + 1];
			// 	}
			//
			// 	return {value, indexOfPipedChars};
			// },
		});
	},
});

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

import flatpickr from 'flatpickr';
import { Dutch } from 'flatpickr/dist/l10n/nl';
import rangePlugin from 'flatpickr/dist/plugins/rangePlugin';
import { Options } from 'flatpickr/dist/types/options';

export const initDateTimePicker = (el: HTMLInputElement): void => {
	const { before, after, maxDate, minDate, readonly } = el.dataset;

	if (readonly) {
		el.readOnly = true;
		return;
	}

	const defaultOpts: Options = {
		locale: Dutch,
		enableTime: true,
		time_24hr: true,
		minDate,
		maxDate,
	};

	// Als after is gezet, dan zorgt before dat deze datetimepicker geinitialiseerd wordt.
	if (after) {
		return;
	}

	// rangePlugin initialiseerd ook de andere input.
	if (before) {
		flatpickr(el, {
			...defaultOpts,
			plugins: [rangePlugin({ input: `#${before}` })],
		});

		return;
	}

	flatpickr(el, defaultOpts);
};

export const initDatePicker = (el: HTMLInputElement): void => {
	const { readonly, minDate, maxDate } = el.dataset;

	if (readonly) {
		el.readOnly = true;
		return;
	}

	flatpickr(el, {
		locale: Dutch,
		minDate,
		maxDate,
	});
};

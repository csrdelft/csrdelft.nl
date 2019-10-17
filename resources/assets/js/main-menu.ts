import $ from 'jquery';
import {docReady} from './util';

docReady(() => {
	$('.dropdown-menu a.dropdown-toggle').on('click', function (e) {
		if (!$(this).next().hasClass('show')) {
			$(this).parents('.dropdown-menu').first().find('.show').removeClass('show');
		}
		const $subMenu = $(this).next('.dropdown-menu');
		$subMenu.toggleClass('show');

		$(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', () => {
			$('.dropdown-submenu .show').removeClass('show');
		});

		return false;
	});

	const searchfield = document.querySelector<HTMLInputElement>('input[type=search].ZoekField');

	if (!searchfield) {
		return;
	}

	document.addEventListener('keydown', (event: KeyboardEvent) => {
		// Geen instantsearch met modifiers
		if (event.ctrlKey || event.altKey || event.metaKey) {
			return;
		}

		if (event.key === 'Escape') {
			searchfield.blur();
			return;
		}

		// Geen instantsearch als we in een input-element of text-area zitten.
		const element = event.target as HTMLElement;
		if (element) {
			const tagName = element.tagName.toUpperCase();

			if (tagName === 'INPUT' || tagName === 'TEXTAREA' || tagName === 'SELECT') {
				return;
			}
		}

		// a-z en 0-9 incl. numpad
		if (/^\w$/.test(event.key)) {
			searchfield.value = '';
			searchfield.focus();
		}
	});
});

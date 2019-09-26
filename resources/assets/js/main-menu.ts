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
});

docReady(() => {
	const searchfield = document.querySelector<HTMLInputElement>('#menu input[type=search]');

	if (!searchfield) { return; }

	$(document).on('keydown', (event: JQuery.KeyDownEvent) => {
		// Geen instantsearch met modifiers
		if (event.ctrlKey || event.altKey || event.metaKey) {
			return;
		}

		// Geen instantsearch als we in een input-element of text-area zitten.
		const element = event.target.tagName.toUpperCase();
		if (element === 'INPUT' || element === 'TEXTAREA' || element === 'SELECT') {
			return;
		}

		// a-z en 0-9 incl. numpad
		if ((event.keyCode > 64 && event.keyCode < 91)
			|| (event.keyCode > 47 && event.keyCode < 58)
			|| (event.keyCode > 95 && event.keyCode < 106)) {
			searchfield.value = '';
			searchfield.dispatchEvent(new Event('focus'));
		}
	});
});

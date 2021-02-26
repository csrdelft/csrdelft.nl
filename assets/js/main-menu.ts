import Hammer from 'hammerjs';
import $ from 'jquery';
import {docReady} from './lib/util';

declare global {
	// Hammer kan een Document als element krijgen, dit zorgt ervoor dat horizontale scroll mogelijk is op mobiel.
	interface HammerStatic {
		new(element: HTMLElement | SVGElement | Document, options?: HammerOptions | undefined): HammerManager;
	}
}

docReady(() => {

	if (!$('#menu').length) { return; }

	let active: string | null = null;

	/**
	 * Zorg ervoor dat de body niet kan scrollen als de overlay zichtbaar is.
	 */
	function toggleScroll() {
		if (active === '#zijbalk') {
			$('body').addClass('overflow-x-hidden');
		} else {
			// Sta toe om te scrollen _nadat_ de animatie klaar is.
			setTimeout(() => $('body').removeClass('overflow-x-hidden'), 300);
		}
	}

	/**
	 * Terug naar gewone view.
	 */
	function reset(event?: Event) {
		if (event && active != null) {
			event.preventDefault();
		}

		active = null;

		$('.target').removeClass('target');

		toggleScroll();
	}

	$('.dropdown-menu a.dropdown-toggle').on('click', function () {
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

	/**
	 * Toggle view met id.
	 * @param id
	 */
	function toggle(id: string) {
		return (event?: Event) => {
			if (event) {
				event.preventDefault();
				event.stopImmediatePropagation();
			}
			if (active === id) {
				reset();
			} else {
				active = id;

				$('.target').not(id).removeClass('target');
				$(id).toggleClass('target');

				toggleScroll();
			}
		};
	}

	$('.trigger[href="#zijbalk"]').on('click', toggle('#zijbalk'));
	$('.cd-page-content,#menu,footer').on('click', reset);

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

			if (element.isContentEditable) {
				return;
			}
		}

		// a-z en 0-9 incl. numpad
		if (/^\w$/.test(event.key)) {
			searchfield.value = '';
			searchfield.focus();
		}
	});

	$(document).on('keyup', (event) => {
		if (event.key == "Escape") {
			reset();
		}
	});

	// Maak het mogelijk om nog tekst te kunnen selecteren.
	delete Hammer.defaults.cssProps.userSelect;

	const hammertime = new Hammer(document, {inputClass: Hammer.TouchInput});

	function swipeDisabled(e: HammerInput) {
		return $(e.target).closest('.disable-swipe, table').length > 0;
	}

	hammertime.on('swiperight', (e) => {
		if (swipeDisabled(e)) {
			return;
		}

		toggle('#zijbalk')();
	});

	hammertime.on('swipeleft', (e) => {
		if (swipeDisabled(e)) {
			return;
		}

		reset();
	});
});

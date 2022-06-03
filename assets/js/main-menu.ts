import Hammer from 'hammerjs';
import {docReady} from './lib/util';
import {select, selectAll} from "./lib/dom";

declare global {
	// Hammer kan een Document als element krijgen, dit zorgt ervoor dat horizontale scroll mogelijk is op mobiel.
	interface HammerStatic {
		new(element: HTMLElement | SVGElement | Document, options?: HammerOptions | undefined): HammerManager;
	}
}

const initSubmenus = () => {
	// Probeer menu te selecteren
	select('#menu')

	const hideSubMenus = () => selectAll('.dropdown-submenu .show').forEach(subMenu => subMenu.classList.remove('show'))

	selectAll('.dropdown-menu a.dropdown-toggle').forEach(el => {
		el.addEventListener('click', e => {
			e.stopPropagation()

			const subMenu = el.nextElementSibling

			if (!subMenu.classList.contains('show')) {
				hideSubMenus()
			}

			subMenu.classList.toggle('show')

			return false
		})

	})

	document.addEventListener('hidden.bs.dropdown', hideSubMenus)

}

const initInstantSearch = () => {
	const searchfield = select<HTMLInputElement>('input[type=search].ZoekField');

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

}

docReady(() => {
	try {
		initSubmenus()
		initInstantSearch()

		const ZIJBALK_SELECTOR = '#zijbalk'
		let active: string | null = null;

		/**
		 * Zorg ervoor dat de body niet kan scrollen als de overlay zichtbaar is.
		 */
		const toggleScroll = () => {
			if (active === '#zijbalk') {
				document.body.classList.add('overflow-x-hidden')
			} else {
				// Sta toe om te scrollen _nadat_ de animatie klaar is.
				setTimeout(() => document.body.classList.remove('overflow-x-hidden'), 300);
			}
		};

		/**
		 * Terug naar gewone view.
		 */
		const reset = (event?: Event) => {
			if (event && active != null) {
				event.preventDefault();
			}

			active = null;

			selectAll('.target').forEach(el => el.classList.remove('target'))

			toggleScroll();
		};


		/**
		 * Toggle view met id.
		 */
		const toggle = (event?: Event) => {
			if (event) {
				event.preventDefault();
				event.stopImmediatePropagation();
			}
			if (active === ZIJBALK_SELECTOR) {
				reset();
			} else {
				active = ZIJBALK_SELECTOR;

				selectAll('.target').forEach(el => {
					if (el.id != 'zijbalk') {
						el.classList.remove('target')
					}
				})

				select(ZIJBALK_SELECTOR).classList.toggle('target')

				toggleScroll();
			}
		};

		selectAll('.trigger[href="#zijbalk"]').forEach(el => el.addEventListener('click', toggle))

		selectAll('.cd-page-content, #menu, footer').forEach(el => el.addEventListener('click', reset))

		document.addEventListener('keydown', e => {
			if (e.key == "Escape") {
				reset();
			}
		})

		// Maak het mogelijk om nog tekst te kunnen selecteren.
		delete Hammer.defaults.cssProps.userSelect;

		const hammertime = new Hammer(document, {inputClass: Hammer.TouchInput});

		const swipeDisabled = (e: HammerInput) => e.target.closest('.disable-swipe, table') != null

		hammertime.on('swiperight', (e) => {
			if (swipeDisabled(e)) {
				return;
			}

			toggle();
		});

		hammertime.on('swipeleft', (e) => {
			if (swipeDisabled(e)) {
				return;
			}

			reset();
		});
	} catch (e) {
		// Geen menu aanwezig
	}
});

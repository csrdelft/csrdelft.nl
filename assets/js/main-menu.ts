import { docReady } from './lib/util';
import { select, selectAll } from './lib/dom';

const initSubmenus = () => {
	// Probeer menu te selecteren
	select('#menu');

	const hideSubMenus = () =>
		selectAll('.dropdown-submenu .show').forEach((subMenu) =>
			subMenu.classList.remove('show')
		);

	selectAll('.dropdown-menu a.dropdown-toggle').forEach((el) => {
		el.addEventListener('click', (e) => {
			e.stopPropagation();

			const subMenu = el.nextElementSibling;

			if (!subMenu.classList.contains('show')) {
				hideSubMenus();
			}

			subMenu.classList.toggle('show');

			return false;
		});
	});

	document.addEventListener('hidden.bs.dropdown', hideSubMenus);
};

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

			if (
				tagName === 'INPUT' ||
				tagName === 'TEXTAREA' ||
				tagName === 'SELECT'
			) {
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
};

docReady(() => {
	try {
		initSubmenus();
		initInstantSearch();
	} catch (e) {
		// Geen menu aanwezig
	}
});

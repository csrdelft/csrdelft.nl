import '../ajax-csrf';
import {docReady} from '../lib/util';
import {select, selectAll} from "../lib/dom";

declare global {
	interface Window {
		$: JQueryStatic;
		jQuery: JQueryStatic;
		formulier: Formulier;
		docReady: (fn: () => void) => void;
	}

	interface Formulier {
		formSubmit(event: Event): void;
	}
}

window.docReady = docReady;

// Versimpelde versie van formSubmit in formulier.js
window.formulier = {formSubmit: (event) => (event.target as HTMLFormElement).form.submit()};

import('jquery').then(({default: $}) => window.$ = window.jQuery = $);

docReady(async () => {
	setTimeout(() => document.body.classList.remove('is-loading'));
	setTimeout(() => import('./extern-defer'))

	const menu = select('#menu');
	const menuKnop = select('.menu-knop');
	document.body.addEventListener('click', (e) => {
		if (!menu.contains(e.target as Node) && !menuKnop.contains(e.target as Node)) {
			menu.classList.remove('show');
		}
	});

	menuKnop.addEventListener('click', (e) => {
		e.preventDefault();

		menu.classList.toggle('show');

		return false;
	});

	selectAll('.expand-dropdown').forEach((knop) => {
		knop.addEventListener('click', (e) => {
			e.preventDefault();

			const parent = knop.parentElement;

			if (!parent) {
				return;
			}

			const parentParent = parent.parentElement;

			if (!parentParent) {
				return;
			}

			const submenu = select('.dropdown', parentParent);

			submenu.classList.toggle('show');

			return false;
		});
	});
});

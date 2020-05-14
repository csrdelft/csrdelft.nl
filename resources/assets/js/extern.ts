import './ajax-csrf';
import {docReady} from './util';

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

window.docReady(() => {
	setTimeout(() => document.body.classList.remove('is-loading'));
	import('jquery').then(($) => {
		window.$ = window.jQuery = $.default;

		import(/* webpackChunkName: "extern-defer" */ './extern-defer');
	});

	const menuKnop = document.querySelector('.menu-knop')!;
	const menu = document.querySelector('#menu') as HTMLDivElement;

	menuKnop.addEventListener('click', (e) => {
		e.preventDefault();

		menu.classList.toggle('show');

		return false;
	});

	const dropdownKnoppen = document.querySelectorAll('.expand-dropdown');

	dropdownKnoppen.forEach((knop) => {
		knop.addEventListener('click', (e) => {
			e.preventDefault();

			const submenu = knop.parentElement!.parentElement!.querySelector('.dropdown') as HTMLDivElement;

			submenu.classList.toggle('show');

			return false;
		});
	});
});

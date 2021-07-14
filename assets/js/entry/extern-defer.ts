/**
 * Wordt geladen als de pagina geladen is.
 */
import axios from 'axios';
import {registerBbContext, registerFormulierContext} from '../context';
import {init} from '../ctx';
import {route} from '../lib/util';
import {select, selectAll} from "../lib/dom";
import hoverintent from "hoverintent"

require('lightbox2');

require('timeago');

const contexts = [];

contexts.push(registerBbContext());

route('/wachtwoord', () => contexts.push(registerFormulierContext()));
route('/forum', () => contexts.push(registerFormulierContext()));

Promise.all(contexts).then(() => init(document.body));

route('/fotoalbum', () => import(/* webpackChunkName: "fotoalbum" */'../page/fotoalbum'));

declare global {
	// Deze functie heeft geen type...
	// eslint-disable-next-line @typescript-eslint/no-namespace
	namespace JQueryUI {
		interface Widget {
			bridge: (newName: string, widget: Widget) => void;
		}
	}

	interface Window {
		bbcode: unknown;
		hoverintent: typeof hoverintent
	}
}

window.hoverintent = hoverintent

const textarea = document.createElement('textarea');

for (const element of selectAll('.lazy-load')) {
	// setTimeout om lazy-load blokken na elkaar te laden ipv allemaal tegelijk.
	setTimeout(() => {
		const innerHTML = element.innerHTML.trim();

		// Sommige browsers encoden de inhoud van de noscript tag.
		if (innerHTML.startsWith('&lt;')) {
			textarea.innerHTML = innerHTML;
			element.outerHTML = textarea.value;
		} else {
			element.outerHTML = innerHTML;
		}
	});
}

try {
	const header = select('#header');
	const banner = select('#banner');

	document.addEventListener('scroll', () => {
		if (banner.getBoundingClientRect().bottom < 0) {
			header.classList.remove('alt');
		} else {
			header.classList.add('alt');
		}
	});
} catch (e) {
	// Geen banner of header
}

try {
	const contactForm = select<HTMLFormElement>('#contact-form')

	const errorContainer = select('#melding')
	const submitButton = contactForm.submitButton as HTMLButtonElement;

	contactForm.addEventListener('submit', async (event) => {
		event.preventDefault();
		errorContainer.innerHTML = '';
		submitButton.disabled = true;
		const formData = new FormData(contactForm);

		try {
			const response = await axios.post('/contactformulier/interesse', formData)
			contactForm.reset();
			submitButton.disabled = false;
			errorContainer.innerHTML = '<div class="alert alert-success">' +
				'<span class="ico accept"></span>' + response.data +
				'</div>';
		} catch (error) {
			submitButton.disabled = false;
			errorContainer.innerHTML = '<div class="alert alert-danger">' +
				'<span class="ico exclamation"></span>' + error.response.data +
				'</div>';
		}

		return false;
	});
} catch (e) {
	// Geen contactform
}

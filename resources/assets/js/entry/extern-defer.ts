/**
 * Wordt geladen als de pagina geladen is.
 */
import axios from 'axios';
import {registerBbContext, registerFormulierContext} from '../context';
import {init} from '../ctx';
import {route} from '../lib/util';

require('lightbox2');
require('../lib/external/jquery.markitup');
require('jquery-hoverintent');

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
	}
}

let hasLoaded = false;

const header = document.querySelector('#header');
const banner = document.querySelector('#banner');

const lazyLoad = () => {
	const textarea = document.createElement('textarea');

	for (const element of document.querySelectorAll('.lazy-load')) {
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
};

// Lazy load after animations have finished and user has scrolled
const loadPage = () => {
	if (!hasLoaded && window.scrollY > 0) {
		hasLoaded = true;
		lazyLoad();
	}

	if (banner && header) {
		if (banner.getBoundingClientRect().bottom < 0) {
			header.classList.remove('alt');
		} else {
			header.classList.add('alt');
		}
	}
};

// resize of scroll zorgt er voor dat beneden de fold geladen wordt.
window.addEventListener('scroll', loadPage);
window.addEventListener('resize', loadPage);

loadPage();

const contactForm = document.querySelector('#contact-form') as HTMLFormElement;

if (contactForm) {
	const errorContainer = document.querySelector('#melding') as HTMLElement;
	const submitButton = contactForm.submitButton as HTMLButtonElement;

	contactForm.addEventListener('submit', (event) => {
		event.preventDefault();
		errorContainer.innerHTML = '';
		submitButton.disabled = true;
		const formData = new FormData(contactForm);
		axios.post('/contactformulier/interesse', formData)
			.then((response) => {
				contactForm.reset();
				submitButton.disabled = false;
				errorContainer.innerHTML = '<div class="alert alert-success">' +
					'<span class="ico accept"></span>' + response.data +
					'</div>';
			})
			.catch((error) => {
				submitButton.disabled = false;
				errorContainer.innerHTML = '<div class="alert alert-danger">' +
					'<span class="ico exclamation"></span>' + error.response.data +
					'</div>';
			});

		return false;
	});
}

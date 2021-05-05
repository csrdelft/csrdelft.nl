/**
 * Wordt geladen als de pagina geladen is.
 */
import axios from 'axios';
import {registerBbContext, registerFormulierContext} from '../context';
import {init} from '../ctx';
import {route} from '../lib/util';
import {select} from "../lib/dom";
import {lazyLoad} from "../lib/lazy-load";

require('fslightbox');

const contexts = [];

contexts.push(registerBbContext());

route('/wachtwoord', () => contexts.push(registerFormulierContext()));
route('/forum', () => contexts.push(registerFormulierContext()));

Promise.all(contexts).then(() => init(document.body));

route('/fotoalbum', () => import('../page/fotoalbum'));

declare global {
	// Deze functie heeft geen type...

	interface Window {
		bbcode: unknown;
	}
}

lazyLoad(".lazy-load")

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

try {
	const remoteLoginCode = select<HTMLFormElement>('.remote-login-code')

	const updateStatus = async () => {
		const response = await fetch('/remote_login_status', {method: 'POST'});
		const remoteLogin = await response.json();

		switch (remoteLogin.status) {
			case 'pending':
				remoteLoginCode.classList.remove('active');
				setTimeout(updateStatus, 1000)
				break;
			case 'active':
				remoteLoginCode.classList.add('active');
				setTimeout(updateStatus, 1000)
				break;
			case 'rejected':
			case 'expired':
				// TODO: Vraag nieuwe code aan
				break;
			case 'accepted':
				remoteLoginCode.classList.remove('active');
				remoteLoginCode.classList.add('accepted');
				// navigeer
				remoteLoginCode.submit();
				break;
		}
	}

	updateStatus();
} catch (e) {
	// Geen remote login
}

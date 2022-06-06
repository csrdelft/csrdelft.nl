/**
 * Wordt geladen als de pagina geladen is.
 */
import axios from 'axios';
import { registerBbContext, registerFormulierContext } from '../context';
import { init } from '../ctx';
import { route } from '../lib/util';
import { select } from '../lib/dom';
import { lazyLoad } from '../lib/lazy-load';

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

lazyLoad('.lazy-load');

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
	const contactForm = select<HTMLFormElement>('#contact-form');

	const errorContainer = select('#melding');
	const submitButton = contactForm.submitButton as HTMLButtonElement;

	contactForm.addEventListener('submit', async (event) => {
		event.preventDefault();
		errorContainer.innerHTML = '';
		submitButton.disabled = true;
		const formData = new FormData(contactForm);

		try {
			const response = await axios.post(
				'/contactformulier/interesse',
				formData
			);
			contactForm.reset();
			submitButton.disabled = false;
			errorContainer.innerHTML =
				'<div class="alert alert-success">' +
				'<i class="fas fa-check" aria-hidden="true"></i>' +
				response.data +
				'</div>';
		} catch (error) {
			submitButton.disabled = false;
			errorContainer.innerHTML =
				'<div class="alert alert-danger">' +
				'<i class="fas fa-ban" aria-hidden="true"></i>' +
				error.response.data +
				'</div>';
		}

		return false;
	});
} catch (e) {
	// Geen contactform
}

try {
	const refreshInterval = 2500;
	const remoteLoginCode = select<HTMLFormElement>('.remote-login-code');

	interface RemoteLogin {
		expires: string;
		status: string;
		uuid: string;
	}

	const updateStatus = async () => {
		const response = await fetch('/remote-login-status', { method: 'POST' });
		const remoteLogin = (await response.json()) as RemoteLogin;

		const expires = new Date(remoteLogin.expires);

		// Ververs de qr code als rejected of verloop is bijna
		if (remoteLogin.status == 'rejected' || +expires - +new Date() < 10_000) {
			remoteLoginCode.classList.remove('active');
			remoteLoginCode.classList.add('loading');

			const refreshResponse = await fetch('/remote-login-refresh', {
				method: 'POST',
			});
			const refresh = (await refreshResponse.json()) as RemoteLogin;

			const qrImage = remoteLoginCode.querySelector('img');
			qrImage.onload = () => remoteLoginCode.classList.remove('loading');
			qrImage.src = '/remote-login-qr?uuid=' + refresh.uuid;

			// Link bestaat alleen in dev
			const link = remoteLoginCode.querySelector('a');
			if (link) link.href = '/rla/' + refresh.uuid;

			setTimeout(updateStatus, refreshInterval);
			return;
		}

		switch (remoteLogin.status) {
			case 'pending':
				remoteLoginCode.classList.remove('active');
				setTimeout(updateStatus, refreshInterval);
				break;
			case 'active':
				remoteLoginCode.classList.add('active');
				setTimeout(updateStatus, refreshInterval);
				break;
			case 'accepted':
				remoteLoginCode.classList.remove('active');
				remoteLoginCode.classList.add('accepted');
				// navigeer
				remoteLoginCode.submit();
				break;
		}
	};

	updateStatus();
} catch (e) {
	// Geen remote login
}

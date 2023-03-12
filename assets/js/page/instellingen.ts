import axios from 'axios';
import { select } from '../lib/dom';
import { urlBase64ToUint8Array } from '../lib/util';
import ctx from '../ctx';

/**
 * Code voor de /instellingen pagina
 */
const instellingVeranderd = () => {
	document
		.querySelectorAll('.instellingen-bericht')
		.forEach((el) => el.classList.remove('d-none'));
};

const meta = document.getElementsByTagName('meta');
const applicationServerKey = meta['vapid-public-key'].content;
let isPushAvailable = false;

const pushAbboneer = async () => {
	if (!isPushAvailable) return;

	const result = await Notification.requestPermission();
	if (result === 'denied') {
		throw new Error('The user explicitly denied the permission request.');
	}
	if (result === 'granted') {
		console.info('The user accepted the permission request.');
	}

	const registration = await navigator.serviceWorker.ready;
	const existingSubscription =
		await registration?.pushManager.getSubscription();
	if (existingSubscription) {
		console.info('User is already subscribed.');
		return;
	}

	// Subscribe to push notifications
	const subscribeOptions = {
		userVisibleOnly: true,
		applicationServerKey: urlBase64ToUint8Array(applicationServerKey),
	};
	const subscription = await registration.pushManager.subscribe(
		subscribeOptions
	);

	if (subscription) {
		try {
			const response = await fetch('/push-abonnement', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(subscription),
			});
			if (!response.ok) {
				throw new Error('Bad status code from server.');
			}

			const data: { success: boolean } = await response.json();
			if (!(data && data.success)) {
				throw new Error('Bad response from server.');
			}

			console.info('Successfully subscribed to push notifications.');
		} catch (error) {
			await subscription?.unsubscribe();
			throw error;
		}
	}
};
const pushDeabboneer = async () => {
	if (!isPushAvailable) return;

	const registration = await navigator.serviceWorker.ready;
	const subscription =
		(await registration?.pushManager.getSubscription()) ?? null;
	if (!subscription) throw new Error('No existing subscription');

	const response = await fetch('/push-abonnement', {
		method: 'DELETE',
		headers: {
			'Content-Type': 'application/json',
		},
		body: JSON.stringify({ endpoint: subscription.endpoint }),
	});

	if (!response.ok) {
		throw new Error('Bad status code from server.');
	}

	const data: { success: boolean } = await response.json();
	if (!(data && data.success)) {
		throw new Error('Bad response from server.');
	}

	const existingSubscription = await subscription?.unsubscribe();
	if (existingSubscription) {
		console.info('Successfully unsubscribed from push notifications.');
	}
};
const pushMeldingenVeranderd = async (ant: string) => {
	switch (ant) {
		case 'ja':
			return pushAbboneer();
		case 'nee':
			return pushDeabboneer();
	}
};

const checkPushAvailability = async () => {
	if (!applicationServerKey) {
		isPushAvailable = false;
		return;
	}

	const supportsPushManager =
		'serviceWorker' in navigator && 'PushManager' in window;

	if (supportsPushManager) {
		const registration = await navigator.serviceWorker.ready;
		const hasPushManager =
			registration !== undefined && 'pushManager' in registration;
		if (hasPushManager) {
			const isIos =
				navigator.userAgent.includes('iPhone') ||
				(navigator.userAgent.includes('Macintosh') && 'ontouchend' in document);

			if (isIos) {
				const isStandalone =
					window.matchMedia('(display-mode: fullscreen)').matches ||
					window.matchMedia('(display-mode: standalone)').matches;

				isPushAvailable = isStandalone;
			} else {
				isPushAvailable = true;
			}
		}
	}

	if (!isPushAvailable) {
		const instellingRow = select('#instelling-forum-meldingPush');
		instellingRow.classList.add('d-none');
	}
};
checkPushAvailability();

const instellingOpslaan = async (ev: Event) => {
	ev.preventDefault();

	const input = ev.target as HTMLElement;

	let href: string | null = null;
	let waarde: string | null = null;

	input.classList.add('loading');

	if (input instanceof HTMLInputElement || input instanceof HTMLSelectElement) {
		if (!input.checkValidity()) {
			return false;
		}

		href = input.dataset.href;
		waarde = input.value;
	} else if (input instanceof HTMLAnchorElement) {
		href = input.href;
	}

	if (!href) {
		throw new Error('Geen url gevonden voor instelling');
	}

	try {
		if (href.includes('meldingPush')) {
			const antwoord = /meldingPush\/(\w+)/g.exec(href);

			await pushMeldingenVeranderd(antwoord[1]);
		}

		await axios.post(href, { waarde });

		instellingVeranderd();

		input.classList.remove('loading');
	} catch (error) {
		console.error(error);
	}
};

ctx.addHandler('.instellingKnop', (el) =>
	el.addEventListener('click', instellingOpslaan)
);
ctx.addHandler('.change-opslaan', (el) =>
	el.addEventListener('change', instellingOpslaan)
);

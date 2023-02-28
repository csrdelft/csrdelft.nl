import axios from 'axios';
import { select } from '../lib/dom';

/**
 * Code voor de /instellingen pagina
 */
const instellingVeranderd = () => {
	document
		.querySelectorAll('.instellingen-bericht')
		.forEach((el) => el.classList.remove('d-none'));
};

const urlBase64ToUint8Array = (base64String: string) => {
	const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
	const base64 = (base64String + padding)
		.replace(/\-/g, '+')
		.replace(/_/g, '/');

	const rawData = window.atob(base64);
	const outputArray = new Uint8Array(rawData.length);

	for (let i = 0; i < rawData.length; ++i) {
		outputArray[i] = rawData.charCodeAt(i);
	}
	return outputArray;
};

const applicationServerKey =
	'BK6nL-UD-kjzpFWXJ6NFkiPEzUEH4diS2BkXBr4ctRz2NU4nyUWZzxLTF2Dulf5spE4EEYVMY2jNmkXhUBTFz2k';
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
		await fetch('/webpush-subscription', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify(subscription),
		})
			.then((response) => {
				if (!response.ok) {
					throw new Error('Bad status code from server.');
				}

				return response.json();
			})
			.then((responseData) => {
				if (!(responseData && responseData.success)) {
					throw new Error('Bad response from server.');
				}
			})
			.catch(async () => {
				await subscription?.unsubscribe();
			});

		console.info('Successfully subscribed to push notifications.');
	}
};
const pushDeabboneer = async () => {
	if (!isPushAvailable) return;

	const registration = await navigator.serviceWorker.ready;
	const subscription =
		(await registration?.pushManager.getSubscription()) ?? null;
	if (!subscription) throw new Error('No existing subscription');

	await fetch('/webpush-subscription', {
		method: 'DELETE',
		headers: {
			'Content-Type': 'application/json',
		},
		body: JSON.stringify({ endpoint: subscription.endpoint }),
	})
		.then((response) => {
			if (!response.ok) {
				throw new Error('Bad status code from server.');
			}

			return response.json();
		})
		.then((responseData) => {
			if (!(responseData && responseData.success)) {
				throw new Error('Bad response from server.');
			}
		});

	const existingSubscription = await subscription?.unsubscribe();
	if (existingSubscription) {
		console.info('Successfully unsubscribed from push notifications.');
	}
};
const pushMeldingenVeranderd = async (ant: string) => {
	switch (ant) {
		case 'ja':
			await pushAbboneer();
			break;
		case 'nee':
			await pushDeabboneer();
			break;
	}
};

const checkPushAvailability = async () => {
	const registration = await navigator.serviceWorker.ready;

	const hasPushManager =
		'serviceWorker' in navigator &&
		'PushManager' in window &&
		registration !== undefined &&
		'pushManager' in registration;

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

	if (!isPushAvailable) {
		const instellingRow = select('#instelling-forum-meldingPush');
		instellingRow.classList.add('d-none');
	}
};
checkPushAvailability();

export const instellingOpslaan = async (ev: Event) => {
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

	if (href.includes('meldingPush')) {
		const antwoord = /meldingPush\/(\w+)/g.exec(href);
		await pushMeldingenVeranderd(antwoord[1]);
	}

	await axios.post(href, { waarde });

	instellingVeranderd();

	input.classList.remove('loading');

	return false;
};

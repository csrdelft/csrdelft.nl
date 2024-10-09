import axios from 'axios';
import { select } from '../lib/dom';
import { docReady, urlBase64ToUint8Array } from '../lib/util';
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
let registration: ServiceWorkerRegistration | undefined;

const pushAbboneer = async () => {
	if (!isPushAvailable) return;

	const result = await Notification.requestPermission();
	if (result === 'denied') {
		throw new Error('The user explicitly denied the permission request.');
	}
	if (result === 'granted') {
		console.info('The user accepted the permission request.');
	}

	registration = await navigator.serviceWorker.ready;
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

	registration = await navigator.serviceWorker.ready;
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

	const isUnsubscribed = await subscription?.unsubscribe();
	if (isUnsubscribed) {
		console.info('Successfully unsubscribed from push notifications.');
	}
};
const pushMeldingenVeranderd = async (ant: string) => {
	switch (ant) {
		case 'ja':
			return await pushAbboneer();
		case 'nee':
			return await pushDeabboneer();
	}
};

const checkPushAvailability = async () => {
	if (!applicationServerKey) {
		isPushAvailable = false;
		return;
	}

	const instellingJa = select<HTMLInputElement>('#inst_forum_meldingPush_ja');
	const instellingNee = select<HTMLInputElement>('#inst_forum_meldingPush_nee');

	const supportsPushManager =
		'serviceWorker' in navigator && 'PushManager' in window;

	if (supportsPushManager) {
		registration = await navigator.serviceWorker.ready;
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

	// Maak de knoppen disabled als push niet beschikbaar is
	if (!isPushAvailable) {
		const instellingGroup = select<HTMLDivElement>(
			'#instelling-forum-meldingPush > div > div.btn-group'
		);
		instellingGroup.setAttribute(
			'title',
			'Push is niet beschikbaar op dit apparaat'
		);

		instellingJa.disabled = true;
		instellingNee.disabled = true;

		instellingNee.checked = true;
	} else {
		// Update de knoppen op basis van bestaande abonnement
		const existingSubscription =
			await registration?.pushManager.getSubscription();
		if (existingSubscription) {
			instellingJa.checked = true;
		} else {
			instellingNee.checked = true;
		}
	}
};
checkPushAvailability();

const instellingOpslaan = async (ev: Event) => {
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
		// Als de instelling meldingPush is moet er meer gebeuren en de instelling wordt op een andere manier geüpdatet
		if (href.includes('meldingPush')) {
			const antwoord = /meldingPush\/(\w+)/g.exec(href);

			await pushMeldingenVeranderd(antwoord[1]);
		} else {
			await axios.post(href, { waarde });

			instellingVeranderd();
		}

		input.classList.remove('loading');
	} catch (error) {
		console.error(error);
		alert('WP Error: ' + error.message);
	}
};

docReady(
	() =>
		void document
			.querySelectorAll('.instellingKnop, .change-opslaan')
			.forEach((el) => el.addEventListener('click', instellingOpslaan))
);

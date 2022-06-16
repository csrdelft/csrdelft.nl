const OFFLINE_VERSION = 1;
const CACHE_NAME = 'offline';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', (event) => {
	event.waitUntil(
		(async () => {
			const cache = await caches.open(CACHE_NAME);
			// Cache 'reload' is om alleen cache van het netwerk te laden
			await cache.add(new Request(OFFLINE_URL, { cache: 'reload' }));
		})()
	);

	// Wacht niet op handmatig registreren
	self.skipWaiting();
});

self.addEventListener('activate', (event) => {
	event.waitUntil(
		(async () => {
			if ('navigationPreload' in self.registration) {
				await self.registration.navigationPreload.enable();
			}
		})()
	);

	self.clients.claim();
});

self.addEventListener('fetch', (event) => {
	// Kijk of de gebruiker een nieuwe pagina laad
	if (event.request.mode === 'navigate') {
		event.respondWith(
			(async () => {
				try {
					const preloadResponse = await event.preloadResponse;
					if (preloadResponse) {
						return preloadResponse;
					}

					// Nog even proberen
					const networkResponse = await fetch(event.request);
					return networkResponse;
				} catch (error) {
					// Als we hier zijn is er geen netwerkverbinding
					console.log('Gebruiker is offline: ', error);

					const cache = await caches.open(CACHE_NAME);
					const cachedResponse = await cache.match(OFFLINE_URL);
					return cachedResponse;
				}
			})()
		);
	} else {
		console.log(`[Service Worker] Fetched resource ${e.request.url}`);
	}
});

// Laat een notificatie zien als het een bericht krijgt van de WebPush API
self.addEventListener('push', (e) => {
	const data = e.data.json();
	self.registration.showNotification(data.title, {
		body: data.body,
		icon: '/favicon.ico',
		image: data.image,
		tag: data.tag,
		data: data.url,
	});
});

// Om de link van het stek bericht in de browser te openen
self.addEventListener(
	'notificationclick',
	function (event) {
		console.log('On notification click: ', event.notification.tag);

		event.notification.close();
		if (clients.openWindow) {
			clients.openWindow(event.notification.data);
		}
	},
	false
);

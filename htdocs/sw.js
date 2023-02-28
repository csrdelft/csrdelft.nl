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
		// console.log(`[Service Worker] Fetched resource ${event.request.url}`);
	}
});

// Laat een notificatie zien als het een bericht krijgt van de WebPush API
self.addEventListener("push", (event) => {
    let messageData = event.data.json();

    event.waitUntil(
        self.registration.showNotification(messageData.title, {
            tag: messageData.tag,
            body: messageData.body,
            icon: "/favicon.ico",
            image: messageData.imageURL ? messageData.imageURL : undefined,
            data: messageData.url,
        })
    );
});

// Om de link van het stek bericht in de browser te openen
self.addEventListener(
    "notificationclick",
    async (event) => {
        event.notification.close();

        const urlToOpen = new URL(event.notification.data, self.location.origin)
            .href;

			// Check of de pagina open is
        const promiseChain = clients
            .matchAll({
                type: "window",
                includeUncontrolled: true,
            })
            .then((windowClients) => {
                let matchingClient = null;

                for (let i = 0; i < windowClients.length; i++) {
                    const windowClient = windowClients[i];
                    if (windowClient.url === urlToOpen) {
                        matchingClient = windowClient;
                        break;
                    }
                }

				// Als pagina niet open is, open het
                if (matchingClient) {
                    return matchingClient.focus();
                } else {
                    return clients.openWindow(urlToOpen);
                }
            });

        event.waitUntil(promiseChain);
    },
    false
);

// Update gegevens in database als de subscription verandert
self.addEventListener(
    "pushsubscriptionchange",
    (event) => {
        event.waitUntil(
            swRegistration.pushManager
                .subscribe(event.oldSubscription.options)
                .then((subscription) => {
                    return fetch("/webpush-subscription", {
                        method: "PUT",
                        body: JSON.stringify(subscription),
                        headers: {
                            "Content-Type": "application/json",
                        },
                    });
                })
        );
    },
    false
);

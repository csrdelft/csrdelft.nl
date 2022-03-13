self.addEventListener('install', (e) => {
	console.log('[Service Worker] Install');
});

// Voor het laden van cache als de stek offline is: https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Offline_Service_workers
self.addEventListener('fetch', (e) => {
	console.log(`[Service Worker] Fetched resource ${e.request.url}`);
});

// Laat een notificatie zien als het een bericht krijgt van de WebPush API
self.addEventListener('push', (e) => {
	const data = e.data.json();
	self.registration.showNotification(data.title, {
		body: data.body,
		icon: '/images/favicon.ico',
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

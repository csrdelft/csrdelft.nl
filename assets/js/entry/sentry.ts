import * as Sentry from '@sentry/browser';

declare global {
	interface Window {
		APP_ENV: string;
		SENTRY_DSN_JS: string;
		USER: string;
	}
}

Sentry.init({
	dsn: window.SENTRY_DSN_JS,
	environment: window.APP_ENV,
	ignoreErrors: [
		/ChunkLoadError/
	]
});

Sentry.setUser({
	username: window.USER,
});

import * as Sentry from '@sentry/browser';
import { BrowserTracing } from '@sentry/tracing';
import { Integration } from '@sentry/types';

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
	integrations: [new BrowserTracing() as unknown as Integration],
	tracesSampleRate: 1.0,
});

Sentry.setUser({
	username: window.USER,
});

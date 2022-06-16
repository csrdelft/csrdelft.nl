import * as Sentry from '@sentry/browser';
import { BrowserTracing } from '@sentry/tracing';
import { Integration } from '@sentry/types';

const meta = document.getElementsByTagName('meta');

const username = meta['sentry-user'].content;
const environment = meta['sentry-app-env'].content;
const dsn = meta['sentry-dsn'].content;

if (username && environment && dsn) {
	Sentry.init({
		dsn,
		environment,
		integrations: [new BrowserTracing() as unknown as Integration],
		tracesSampleRate: 1.0,
	});

	Sentry.setUser({
		username,
	});
}

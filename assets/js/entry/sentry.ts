import * as Sentry from '@sentry/browser';
import { BrowserTracing } from '@sentry/tracing';
import { Integration } from '@sentry/types';
import { select } from '../lib/dom';

try {
	const username = select<HTMLMetaElement>('meta[sentry-user]').content;
	const environment = select<HTMLMetaElement>('meta[sentry-app-env]').content;
	const dsn = select<HTMLMetaElement>('meta[sentry-dsn]').content;

	Sentry.init({
		dsn,
		environment,
		integrations: [new BrowserTracing() as unknown as Integration],
		tracesSampleRate: 1.0,
	});

	Sentry.setUser({
		username,
	});
} catch (e) {
	// ignored
}

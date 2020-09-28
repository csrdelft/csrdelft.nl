import axios from 'axios';

interface Token {
	'X-CSRF-ID': string;
	'X-CSRF-VALUE': string;
}

function getCsrfHeaders(): Token {
	const idMetaTag = document.querySelector<HTMLMetaElement>('meta[property=\'X-CSRF-ID\']')
	const valueMetaTag = document.querySelector<HTMLMetaElement>('meta[property=\'X-CSRF-VALUE\']');

	if (!idMetaTag || !valueMetaTag) {
		throw new Error("Geen CSRF meta tag gevonden")
	}

	return {
		'X-CSRF-ID': idMetaTag.content,
		'X-CSRF-VALUE': valueMetaTag.content,
	};
}

// Extern heeft geen jquery
if (window.$) {
	window.$.ajaxPrefilter((options, originalOptions, jqXHR) => {
		if (!options.crossDomain) {
			const token = getCsrfHeaders();
			jqXHR.setRequestHeader('X-CSRF-ID', token['X-CSRF-ID']);
			jqXHR.setRequestHeader('X-CSRF-VALUE', token['X-CSRF-VALUE']);
		}
	});
}

axios.interceptors.request.use((config) => {
	if (!config.url) { return config; }

	if (config.url.startsWith(window.location.origin) || config.url.startsWith('/')) {
		return {
			...config,
			headers: {
				...config.headers,
				...getCsrfHeaders(),
			},
		};
	} else {
		return config;
	}
});

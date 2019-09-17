import axios from 'axios';

interface Token {
	'X-CSRF-ID': string;
	'X-CSRF-VALUE': string;
}

function getCsrfHeaders(): Token {
	return {
		'X-CSRF-ID': (document.querySelector('meta[property=\'X-CSRF-ID\']') as HTMLMetaElement).content!,
		'X-CSRF-VALUE': (document.querySelector('meta[property=\'X-CSRF-VALUE\']') as HTMLMetaElement).content!,
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

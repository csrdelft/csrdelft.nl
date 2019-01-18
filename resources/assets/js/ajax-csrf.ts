import axios from 'axios';
import $ from 'jquery';

interface Token {
	'X-CSRF-ID': string;
	'X-CSRF-VALUE': string;
}

function getCsrfHeaders(): Token {
	return {
		'X-CSRF-ID': $('meta[property=\'X-CSRF-ID\']').attr('content')!,
		'X-CSRF-VALUE': $('meta[property=\'X-CSRF-VALUE\']').attr('content')!,
	};
}

$.ajaxPrefilter((options, originalOptions, jqXHR) => {
	if (!options.crossDomain) {
		const token = getCsrfHeaders();
		jqXHR.setRequestHeader('X-CSRF-ID', token['X-CSRF-ID']);
		jqXHR.setRequestHeader('X-CSRF-VALUE', token['X-CSRF-VALUE']);
	}
});

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

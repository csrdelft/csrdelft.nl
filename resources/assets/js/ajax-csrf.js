import $ from 'jquery';
import axios from 'axios';

function getCsrfToken() {
    return {
        'X-CSRF-ID': $('meta[property=\'X-CSRF-ID\']').attr('content'),
        'X-CSRF-VALUE': $('meta[property=\'X-CSRF-VALUE\']').attr('content')
    };
}

$.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
    if (!options.crossDomain) {
        let token = getCsrfToken();
        jqXHR.setRequestHeader('X-CSRF-ID', token['X-CSRF-ID']);
        jqXHR.setRequestHeader('X-CSRF-VALUE', token['X-CSRF-VALUE']);
    }
});

axios.interceptors.request.use((config) => {
	if (config.url.startsWith(window.location.origin) || config.url.startsWith('/')) {
		return {
			...config,
			headers: {
				...config.headers,
				...getCsrfToken(),
			}
		};
	} else {
		return config;
	}
});

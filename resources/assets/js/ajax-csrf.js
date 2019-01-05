import $ from 'jquery';

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

export const AXIOS_LOCAL_CSRF_CONF = {
    headers: getCsrfToken()
};
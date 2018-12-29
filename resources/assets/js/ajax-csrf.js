import $ from 'jquery';

$.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
    if (!options.crossDomain) {
        jqXHR.setRequestHeader('X-CSRF-ID', $('meta[property=\'X-CSRF-ID\']').attr('content'));
        jqXHR.setRequestHeader('X-CSRF-VALUE', $('meta[property=\'X-CSRF-VALUE\']').attr('content'));
    }
});
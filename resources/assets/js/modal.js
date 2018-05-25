import $ from 'jquery';

/**
 * @param {string} htmlString
 * @returns {boolean}
 */
export function modalOpen(htmlString = '') {
    if ($(this).hasClass('confirm') && !confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
        htmlString.preventDefault();
        return false;
    }

    let modal = $('#modal'),
        modalWrapper = $('#modal-wrapper');

    if (typeof htmlString === 'string' && htmlString !== '') {
        modal.html(htmlString);
        modal.find('input:visible:first').focus();
    }
    else {
        modalWrapper.modal('hide');
        modal.html('');
    }

    modalWrapper.modal();

    return true;
}

export function modalClose() {
    $('#modal-wrapper').modal('hide');
}
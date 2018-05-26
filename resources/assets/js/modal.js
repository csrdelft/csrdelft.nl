import $ from 'jquery';

/**
 * @param {string} htmlString
 * @returns {boolean}
 */
export function modalOpen(htmlString = '') {
    if ($(this).hasClass('confirm') && !confirm($(this).attr('title') + '.\n\nWeet u het zeker?')) {
        return false;
    }

    let modal = $('#modal');

    if (modal.html() === '' && htmlString === '')
        return false;

    modal.modal();

    if (typeof htmlString === 'string' && htmlString !== '') {
        modal.html(htmlString);
        modal.find('input:visible:first').trigger('focus');
    }

    return true;
}

export function modalClose() {
    $('#modal').modal('hide');
}
import $ from 'jquery';

export function modalOpen(htmlString = '') {
    let modal = $('#modal'),
        modalBackdrop = $('.modal-backdrop');

    if (modal.html() === '' && htmlString === '') {
        return false;
    }

    if (modalBackdrop.length){
        modalBackdrop.remove();
    }

    modal.modal('show');
    $(document.body).trigger('modalOpen');

    if (htmlString !== '') {
        modal.html(htmlString);
        modal.find('input:visible:first').trigger('focus');
    }

    return true;
}

export function modalClose() {
    $('#modal').modal('hide');
    $(document.body).trigger('modalClose');
}

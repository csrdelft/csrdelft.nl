import $ from 'jquery';

export function modalOpen(htmlString = ''): boolean {
	const modal = $('#modal');
	const modalBackdrop = $('.modal-backdrop');

	if (modal.html() === '' && htmlString === '') {
		return false;
	}

	if (modalBackdrop.length) {
		modalBackdrop.remove();
	}

	if (htmlString !== '') {
		modal.replaceWith(htmlString);
		modal.find('input:visible:first').trigger('focus');
	}

	modal.modal('show');
	$(document.body).trigger('modalOpen');

	return true;
}

export function modalClose(): void {
	$('#modal').modal('hide');
	$(document.body).trigger('modalClose');
}

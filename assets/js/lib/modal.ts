import { Modal } from 'bootstrap';
import { select } from './dom';

export function modalOpen(htmlString = ''): boolean {
	const modalEl = document.getElementById('modal');
	const modal = Modal.getInstance(modalEl) ?? new Modal(modalEl);

	if (modalEl.innerHTML === '' && htmlString === '') {
		return false;
	}

	try {
		// Verwijder mogelijk bestaande backdrop
		select('.modal-backdrop').remove();
	} catch (e) {
		// negeer
	}

	if (htmlString !== '') {
		modalEl.replaceWith(htmlString);
		Array.from(modalEl.querySelectorAll('input'))
			.find((el) => window.getComputedStyle(el).display != 'none')
			?.dispatchEvent(new FocusEvent('focus'));
	}

	modal.show();
	document.dispatchEvent(new Event('modalOpen'));

	return true;
}

export function modalClose(): void {
	const modal = Modal.getInstance(document.getElementById('modal'));
	if (modal) {
		modal.hide();
	}
	document.dispatchEvent(new Event('modalClose'));
}

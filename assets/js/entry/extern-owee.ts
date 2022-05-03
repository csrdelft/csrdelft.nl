import axios from 'axios';
import '../ajax-csrf';
import {docReady} from '../lib/util';

declare global {
	interface Window {
		$: JQueryStatic;
		jQuery: JQueryStatic;
		docReady: (fn: () => void) => void;
	}
}

window.docReady = docReady;

docReady(() => {
	const oweeForm = document.querySelector('#owee-form') as HTMLFormElement;

	if (oweeForm) {
		const errorContainer = document.querySelector('#melding') as HTMLElement;
		const submitButton = oweeForm.submitButton as HTMLButtonElement;
		const formulierVelden = document.querySelector('#formulierVelden') as HTMLElement;

		oweeForm.addEventListener('submit', (event) => {
			event.preventDefault();
			errorContainer.innerHTML = '';
			submitButton[0].disabled = true;
			submitButton[1].disabled = true;
			submitButton[2].disabled = true;
			const formData = new FormData(oweeForm);
			axios.post('/contactformulier/owee', formData)
				.then((response) => {
					oweeForm.reset();
					submitButton[0].disabled = false;
					submitButton[1].disabled = false;
					submitButton[2].disabled = false;
					errorContainer.innerHTML = '<div class="alert alert-success">' +
						'<span class="ico accept"></span>' + response.data +
						'</div>';
					formulierVelden.style.display = 'none';
				})
				.catch((error) => {
					submitButton[0].disabled = false;
					submitButton[1].disabled = false;
					submitButton[2].disabled = false;
					errorContainer.innerHTML = '<div class="alert alert-danger">' +
						'<span class="ico exclamation"></span>' + error.response.data +
						'</div>';
				});

			return false;
		});
	}
});

import axios from 'axios';
import {docReady} from '../lib/util';

/**
 * Code voor de /instellingen pagina
 */
const instellingVeranderd = () => {
	document.querySelectorAll('.instellingen-bericht')
		.forEach((el) => el.classList.remove('d-none'));
};

const instellingOpslaan = async (ev: Event) => {
	ev.preventDefault();

	const input = ev.target as HTMLElement;

	let href = '';
	let waarde = '';

	input.classList.add('loading');

	if (input instanceof HTMLInputElement || input instanceof HTMLSelectElement) {
		if (!input.checkValidity()) {
			return false;
		}

		href = input.dataset.href!;
		waarde = input.value;
	} else if (input instanceof HTMLAnchorElement) {
		href = input.href;
	}

	await axios.post(href, {waarde});

	instellingVeranderd();

	input.classList.remove('loading');

	return false;
};

docReady(() => {
	document.querySelectorAll('.instellingKnop')
		.forEach((el) => el.addEventListener('click', instellingOpslaan));

	document.querySelectorAll('.change-opslaan')
		.forEach((el) => el.addEventListener('change', instellingOpslaan));
});

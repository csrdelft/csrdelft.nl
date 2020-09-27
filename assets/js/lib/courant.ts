import $ from 'jquery';

/**
 * @param {string} id
 */
export function importAgenda(id: string): void {
	const input = document.getElementById(id) as HTMLInputElement;

	$.ajax({
		cache: false,
		data: '',
		type: 'POST',
		url: '/agenda/courant',
	}).done((data) => input.value += '\n' + data);
}

import $ from 'jquery';

/**
 * @param {string} id
 */
export function importAgenda(id: string) {
	let input = document.getElementById(id) as HTMLInputElement

	$.ajax({
		type: 'POST',
		cache: false,
		url: '/agenda/courant/',
		data: ''
	}).done((data) => input.value += '\n' + data);
}

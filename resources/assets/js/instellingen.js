import $ from 'jquery';

/**
 * Code voor de /instellingen pagina
 */

function instellingVeranderd() {
	console.log('instellingen veranderd');
	$('.instellingen-bericht').removeClass('d-none');
}

function instellingOpslaan() {
	let input = $(this);

	let href = input.data('href');

	input.addClass('loading');

	$.ajax({
		url: href,
		method: 'POST',
		data: {
			waarde: input.val()
		}
	}).then(() => {
		instellingVeranderd();
		input.removeClass('loading');
	});
}

$(() => {
	console.log('instellingen geladen');
	$('.instellingKnop').on('click', instellingVeranderd);

	$('.change-opslaan').on('change', instellingOpslaan);
});

import $ from 'jquery';

/**
 * Code voor de /instellingen pagina
 */

function instellingVeranderd() {
	$('.instellingen-bericht').removeClass('d-none');
}

function instellingOpslaan(ev : JQuery.ChangeEvent) {
	if (ev.target!.checkValidity()) {
		let input = $(ev.target);

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
}

$(() => {
	$('.instellingKnop').on('click', instellingVeranderd);
	$('.change-opslaan').on('change', (ev) => instellingOpslaan(ev));
});

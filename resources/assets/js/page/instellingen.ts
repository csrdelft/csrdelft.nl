import $ from 'jquery';

/**
 * Code voor de /instellingen pagina
 */

function instellingVeranderd() {
	$('.instellingen-bericht').removeClass('d-none');
}

function instellingOpslaan(ev: JQuery.ChangeEvent) {
	if (ev.target.checkValidity()) {
		const input = $(ev.target);

		const href = input.data('href');

		input.addClass('loading');

		$.ajax({
			data: {
				waarde: input.val(),
			},
			method: 'POST',
			url: href,
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

import $ from 'jquery';

require('../sass/effect/trein.scss');

$(function () {
	const treinen = [
		'trein ns-ddz-4',
		'trein ns-ddz-6',
		'trein ns-icm-3',
		'trein ns-icm-4',
		'trein ns-icr-7',
		'trein ns-icr-9',
		'trein flirt3-blauw',
		'trein arriva',
		'trein ns-virm-4',
		'trein ns-virm-6',
		'trein ns-sgmm-2',
		'trein ns-sgmm-3',
		'trein ns-flirt-3',
		'trein ns-slt-6',
		'trein ns-sng-4',
		'trein rnet-gtw',
	];

	const rails = $('.rails');

	setTimeout(() => {
		stuurTrein();
		setInterval(() => {
			stuurTrein();
		}, 18000);
	}, Math.random() * 5000 + 5000);

	function stuurTrein() {
		let trein = $('<div>');
		trein.attr('class', treinen[Math.floor((Math.random() * treinen.length))]);

		rails.append(trein);

		setTimeout(() => {
			trein.remove();
		}, 13000);

		return trein;
	}
});

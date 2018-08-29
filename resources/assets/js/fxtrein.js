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
	];

	const rails = $('.rails');

	setInterval(() => {
		setTimeout(() => {
			stuurTrein(treinen[Math.floor((Math.random() * treinen.length))]);
		}, Math.floor((Math.random() * treinen.length)) * 2000);
	}, 10000);

	function stuurTrein(type) {
		let trein = $('<div>');
		trein.attr('class', type);

		rails.append(trein);

		setTimeout(() => {
			trein.remove();
		}, 13000);

		return trein;
	}
});

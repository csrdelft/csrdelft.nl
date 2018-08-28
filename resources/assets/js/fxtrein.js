import $ from 'jquery';

require('../sass/effect/trein.scss');

$(function () {
	const treinen = [
		'trein ns-ddz', 'trein ns-koploper', 'trein ns-intercity',
	];

	const rails = $('.rails');

	setInterval(() => {
		setTimeout(() => {
			stuurTrein(treinen[Math.floor((Math.random() * treinen.length))]);
		}, Math.floor((Math.random() * treinen.length)) * 2000);
	}, 10000);

	stuurTrein('ns-koploper');

	function stuurTrein(type) {
		let trein = $('<div>');
		trein.attr('class', type);

		rails.append(trein);

		setTimeout(() => {
			trein.remove();
		}, 8000);

		return trein;
	}
});

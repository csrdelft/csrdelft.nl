import $ from 'jquery';

import '../../scss/effect/minion.scss';

/**
 * minion.js  |  P.W.G. Brussee (brussee@live.nl)
 *
 * requires jQuery
 */

declare global {
	interface Window {
		superman: () => void;
	}
}

function createPosition(): JQuery.Coordinates {
	const h = window.innerHeight - 50;
	const w = window.innerWidth - 50;
	const nh = Math.floor(Math.random() * h);
	const nw = Math.floor(Math.random() * w);

	return { top: nh, left: nw };
}

function calcSpeed(prev: JQuery.Coordinates, next: JQuery.Coordinates): number {
	const x = Math.abs(prev.left - next.left);
	const y = Math.abs(prev.top - next.top);
	const greatest = x > y ? x : y;
	const speedModifier = 0.5;

	return Math.ceil(greatest / speedModifier);
}

function animateMinion() {
	const $minion = $('#minion');

	if (!$minion.hasClass('superman')) {
		return;
	}

	const newq = createPosition();
	const oldq = $minion.offset() || { top: 0, left: 0 };
	const speed = calcSpeed(oldq, newq);

	$minion.animate(newq, speed, animateMinion);
}

$(() => {
	const minion = $('#minion');
	minion.on('dblclick', () => {
		minion.toggleClass('superman');
		animateMinion();
	});
});
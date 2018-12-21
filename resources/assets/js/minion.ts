import $ from 'jquery';

/**
 * minion.js  |  P.W.G. Brussee (brussee@live.nl)
 *
 * requires jQuery
 */

declare global {
    interface Window { superman: () => void; }
}

function createPosition(): JQuery.Coordinates {
    let h = window.innerHeight - 50,
        w = window.innerWidth - 50,
        nh = Math.floor(Math.random() * h),
        nw = Math.floor(Math.random() * w);

    return {top: nh, left: nw};
}

function calcSpeed(prev: JQuery.Coordinates, next: JQuery.Coordinates): number {
    let x = Math.abs(prev.left - next.left),
        y = Math.abs(prev.top - next.top),
        greatest = x > y ? x : y,
        speedModifier = 0.5;

    return Math.ceil(greatest / speedModifier);
}

function animateMinion() {
    let $minion = $('#minion');

    if (!$minion.hasClass('superman')) {
        return;
    }

    let newq = createPosition(),
        oldq = $minion.offset() || {top: 0, left: 0},
        speed = calcSpeed(oldq, newq);

    $minion.animate(newq, speed, animateMinion);
}
$(() => {
	const minion = $('#minion');
	minion.on('dblclick', () => {
		minion.toggleClass('superman');
		animateMinion();
	});
});

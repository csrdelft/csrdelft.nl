/**
 * minion.js  |  P.W.G. Brussee (brussee@live.nl)
 *
 * requires jQuery
 */

function makeNewPosition() {
	let h = $(window).height() - 50,
		w = $(window).width() - 50,
		nh = Math.floor(Math.random() * h),
		nw = Math.floor(Math.random() * w);

	return [nh, nw];
}

function calcSpeed(prev, next) {
	let x = Math.abs(prev[1] - next[1]),
		y = Math.abs(prev[0] - next[0]),
		greatest = x > y ? x : y,
		speedModifier = 0.5;

	return Math.ceil(greatest / speedModifier);
}

function animateMinion() {
	let $minion = $('#minion');

	if (!$minion.hasClass('superman')) {
		return;
	}

	let newq = makeNewPosition(),
		oldq = $minion.offset(),
		speed = calcSpeed([oldq.top, oldq.left], newq);

	$minion.animate({
		top: newq[0],
		left: newq[1]
	}, speed, function () {
		animateMinion();
	});
}

/**
 * @see templates/minion.tpl
 */
window.superman = function() {
	$('#minion').toggleClass('superman');
	animateMinion();
};

/**
 * minion.js  |  P.W.G. Brussee (brussee@live.nl)
 *
 * requires jQuery
 */

class Position {
    /**
     * @param {number} top
     * @param {number} left
     */
	constructor(top, left) {
		this.top = top;
		this.left = left;
	}

    /**
	 * @returns {Position}
     */
	static create() {
        let h = $(window).height() - 50,
            w = $(window).width() - 50,
            nh = Math.floor(Math.random() * h),
            nw = Math.floor(Math.random() * w);

        return new Position(nh, nw);
	}
}

/**
 * @param {Position} prev
 * @param {Position} next
 * @returns {number}
 */
function calcSpeed(prev, next) {
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

	let newq = Position.create(),
		oldq = $minion.offset(),
		position = new Position(oldq.top, oldq.left),
		speed = calcSpeed(position, newq);

	$minion.animate({
		top: newq.top,
		left: newq.left
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

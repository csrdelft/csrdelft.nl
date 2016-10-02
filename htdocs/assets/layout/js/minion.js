/**
 * minion.js	|	P.W.G. Brussee (brussee@live.nl)
 * 
 * requires jQuery
 */

function superman() {
	$('#minion').toggleClass('superman');
	animateMinion();
}

function makeNewPosition() {
    var h = $(window).height() - 50;
    var w = $(window).width() - 50;
    var nh = Math.floor(Math.random() * h);
    var nw = Math.floor(Math.random() * w);
    return [nh, nw];
}

function animateMinion() {
	if (!$('#minion').hasClass('superman')) {
		return;
	}
    var newq = makeNewPosition();
    var oldq = $('#minion').offset();
    var speed = calcSpeed([oldq.top, oldq.left], newq);
    $('#minion').animate({
        top: newq[0],
        left: newq[1]
    }, speed, function() {
        animateMinion();
    });
}

function calcSpeed(prev, next) {
    var x = Math.abs(prev[1] - next[1]);
    var y = Math.abs(prev[0] - next[0]);
    var greatest = x > y ? x : y;
    var speedModifier = 0.5;
    var speed = Math.ceil(greatest / speedModifier);
    return speed;
}
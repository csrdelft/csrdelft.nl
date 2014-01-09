/**
 * dragobject.js	|	P.W.G. Brussee (brussee@live.nl)
 * 
 * requires jQuery
 */

$(document).ready(function() {
	window.addEventListener('mousedown', startDrag, false);
	window.addEventListener('mouseup', stopDrag, false);
});

var dragobjectID;
var oldX;
var oldY;

function startDrag(e) {
	e = e || window.event;
	var tag = e.target.tagName.toUpperCase();
	var overflow = $(e.target).css('overflow');
	if (tag === 'SELECT' || tag === 'INPUT' || tag === 'TEXTAREA' || overflow === 'auto' || overflow === 'scroll') { // sliding scrollbar of dropdown menu or input field
		return;
	}
	dragobjectID = $(e.target).attr('id');
	if (typeof dragobjectID === 'undefined' || dragobjectID === false || !$('#'+dragobjectID).hasClass('dragobject')) {
		dragobjectID = $(e.target).closest('.dragobject').attr('id');
	}
	if (typeof dragobjectID !== 'undefined' && dragobjectID !== false) {
		oldX = mouseX(e);
		oldY = mouseY(e);
		window.addEventListener('mousemove', mouseMoveHandler, true);
	}
}
function stopDrag(e) {
	window.removeEventListener('mousemove', mouseMoveHandler, true);
}
function mouseMoveHandler(e) {
	e = e || window.event;
	var newX = mouseX(e);
	var newY = mouseY(e);
	var oldL = $('#'+dragobjectID).offset().left - (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
	var oldT = $('#'+dragobjectID).offset().top - (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	$('#'+dragobjectID).css('left', (oldL + newX - oldX) + 'px');
	$('#'+dragobjectID).css('top', (oldT + newY - oldY) + 'px');
	oldX = newX;
	oldY = newY;
}
function mouseX(e) {
	if (e.pageX) {
	  return e.pageX;
	}
	if (e.clientX) {
		return e.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
	}
	return null;
}
function mouseY(e) {
	if (e.pageY) {
		return e.pageY;
	}
	if (e.clientY) {
		return e.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	}
	return null;
}
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
var offsetX = 0;
var offsetY = 0;
function startDrag(e) {
	e = e || window.event;
	dragobjectID = $(e.target).attr('id');
	if (typeof dragobjectID === 'undefined' || dragobjectID === false || !$('#'+dragobjectID).hasClass('dragobject')) {
		dragobjectID = $(e.target).parent('.dragobject').attr('id');
	}
	if (typeof dragobjectID !== 'undefined' && dragobjectID !== false) {
		offsetX = mouseX(e);
		offsetY = mouseY(e);
		window.addEventListener('mousemove', mouseMoveHandler, true);
	}
}
function stopDrag(e) {
	window.removeEventListener('mousemove', mouseMoveHandler, true);
}
function mouseMoveHandler(e) {
	e = e || window.event;
	var x = mouseX(e);
	var y = mouseY(e);
	if (x !== offsetX || y !== offsetY) {
		var l = $('#'+dragobjectID).offset().left - (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
		var t = $('#'+dragobjectID).offset().top - (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
		$('#'+dragobjectID).css('left', (l + x - offsetX) + 'px');
		$('#'+dragobjectID).css('top', (t + y - offsetY) + 'px');
		offsetX = x;
		offsetY = y;
	}
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
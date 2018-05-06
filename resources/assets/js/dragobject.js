/**
 * dragobject.js	|	P.W.G. Brussee (brussee@live.nl)
 *
 * requires jQuery
 */

const $ = require('jquery');

$(document).ready(function () {
	let dragObjectId = false,
		dragged,
		oldX,
		oldY;

	function docScrollLeft() {
		return (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
	}
	function docScrollTop() {
		return (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	}

	function mouseX(e) {
		if (e.pageX) {
			return e.pageX;
		}

		if (e.clientX) {
			return e.clientX + docScrollLeft();
		}

		return null;
	}
	function mouseY(e) {
		if (e.pageY) {
			return e.pageY;
		}

		if (e.clientY) {
			return e.clientY + docScrollTop();
		}

		return null;
	}

	function dragObjLeft() {
		return $('#' + dragObjectId).offset().left - docScrollLeft();
	}
	function dragObjTop() {
		return $('#' + dragObjectId).offset().top - docScrollTop();
	}

	function startDrag(e) {
		e = e || window.event;
		let tag = e.target.tagName.toUpperCase();
		let overflow = $(e.target).css('overflow');
		if ((tag !== 'DIV' && tag !== 'H1') || overflow === 'auto' || overflow === 'scroll') { // sliding scrollbar of dropdown menu or input field
			return;
		}
		dragObjectId = $(e.target).attr('id');
		if (typeof dragObjectId === 'undefined' || dragObjectId === false || !$('#' + dragObjectId).hasClass('dragobject')) {
			dragObjectId = $(e.target).closest('.dragobject').attr('id');
		}
		if (typeof dragObjectId !== 'undefined' && dragObjectId !== false) {
			oldX = mouseX(e);
			oldY = mouseY(e);
			window.addEventListener('mousemove', mouseMoveHandler, true);
		}
		else {
			dragObjectId = false;
		}
		dragged = false;
	}
	function stopDrag() {
		if (!dragObjectId) {
			return;
		}
		window.removeEventListener('mousemove', mouseMoveHandler, true);
		if (dragged) {
			let dragObject = $('#' + dragObjectId);
			let top, left;
			if (dragObject.hasClass('dragvertical') || dragObject.hasClass('draghorizontal')) {
				top = dragObject.scrollTop();
				left = dragObject.scrollLeft();
			}
			else {
				top = dragObjTop();
				left = dragObjLeft();
			}
			$.post('/tools/dragobject', {
				id: dragObjectId,
				coords: {
					top: top,
					left: left
				}
			});
			dragged = false;
		}
		dragObjectId = false;
	}
	function mouseMoveHandler(e) {
		if (!dragObjectId) {
			return;
		}
		let dragobject = $('#' + dragObjectId);
		e = e || window.event;
		let newX = mouseX(e);
		let newY = mouseY(e);
		dragged = dragobject.hasClass('savepos');
		if (dragobject.hasClass('dragvertical')) {
			dragobject.scrollTop(dragobject.scrollTop() + oldY - newY);
		}
		else if (dragobject.hasClass('draghorizontal')) {
			dragobject.scrollLeft(dragobject.scrollLeft() + oldX - newX);
		}
		else {
			dragobject.css({
				'top': (dragObjTop() + newY - oldY) + 'px',
				'left': (dragObjLeft() + newX - oldX) + 'px'
			});
		}
		oldX = newX;
		oldY = newY;
	}

	window.addEventListener('mousedown', startDrag, false);
	window.addEventListener('mouseup', stopDrag, false);
});

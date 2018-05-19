/**
 * dragobject.js	|	P.W.G. Brussee (brussee@live.nl)
 *
 * requires jQuery
 */

import $ from 'jquery';

window.dragObjectId = false;

$(function () {
	let dragged,
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
		return $('#' + window.dragObjectId).offset().left - docScrollLeft();
	}
	function dragObjTop() {
		return $('#' + window.dragObjectId).offset().top - docScrollTop();
	}

	function startDrag(e) {
		e = e || window.event;
		let tag = e.target.tagName.toUpperCase();
		let overflow = $(e.target).css('overflow');
		if ((tag !== 'DIV' && tag !== 'H1') || overflow === 'auto' || overflow === 'scroll') { // sliding scrollbar of dropdown menu or input field
			return;
		}
        window.dragObjectId = $(e.target).attr('id');
		if (typeof window.dragObjectId === 'undefined' || window.dragObjectId === false || !$('#' + window.dragObjectId).hasClass('dragobject')) {
            window.dragObjectId = $(e.target).closest('.dragobject').attr('id');
		}
		if (typeof window.dragObjectId !== 'undefined' && window.dragObjectId !== false) {
			oldX = mouseX(e);
			oldY = mouseY(e);
			window.addEventListener('mousemove', mouseMoveHandler, true);
		}
		else {
            window.dragObjectId = false;
		}
		dragged = false;
	}
	function stopDrag() {
		if (!window.dragObjectId) {
			return;
		}
		window.removeEventListener('mousemove', mouseMoveHandler, true);
		if (dragged) {
			let dragObject = $('#' + window.dragObjectId);
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
				id: window.dragObjectId,
				coords: {
					top: top,
					left: left
				}
			});
			dragged = false;
		}
        window.dragObjectId = false;
	}
	function mouseMoveHandler(e) {
		if (!window.dragObjectId) {
			return;
		}
		let dragobject = $('#' + window.dragObjectId);
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

/**
 * dragobject.js	|	P.W.G. Brussee (brussee@live.nl)
 */

import $ from 'jquery';

export const dragObject = {
	id: false
};

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

    /**
     * @param {MouseEvent} e
     * @returns {number|null}
     */
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
		return $('#' + dragObject.id).offset().left - docScrollLeft();
	}
	function dragObjTop() {
		return $('#' + dragObject.id).offset().top - docScrollTop();
	}

    /**
     * @param {MouseEvent} e
     */
	function startDrag(e) {
		e = e || window.event;
		let tag = e.target.tagName.toUpperCase();
		let overflow = $(e.target).css('overflow');
		if ((tag !== 'DIV' && tag !== 'H1') || overflow === 'auto' || overflow === 'scroll') { // sliding scrollbar of dropdown menu or input field
			return;
		}
		dragObject.id = $(e.target).attr('id');
		if (typeof dragObject.id === 'undefined' || dragObject.id === false || !$('#' + dragObject.id).hasClass('dragobject')) {
            dragObject.id = $(e.target).closest('.dragobject').attr('id');
		}
		if (typeof dragObject.id !== 'undefined' && dragObject.id !== false) {
			oldX = mouseX(e);
			oldY = mouseY(e);
			window.addEventListener('mousemove', mouseMoveHandler, true);
		}
		else {
            dragObject.id = false;
		}
		dragged = false;
	}

	function stopDrag() {
		if (!dragObject.id) {
			return;
		}
		window.removeEventListener('mousemove', mouseMoveHandler, true);
		if (dragged) {
			let dragObject = $('#' + dragObject.id);
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
				id: dragObject.id,
				coords: {
					top,
					left
				}
			});
			dragged = false;
		}
        dragObject.id = false;
	}

    /**
     * @param {MouseEvent} e
     */
	function mouseMoveHandler(e) {
		if (!dragObject.id) {
			return;
		}
		let dragobject = $('#' + dragObject.id);
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

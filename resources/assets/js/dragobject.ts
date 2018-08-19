/**
 * dragobject.js	|	P.W.G. Brussee (brussee@live.nl)
 */

import $ from 'jquery';

export const dragObject: { el: JQuery<Element> | undefined; } = {
	el: undefined
};

$(function () {
	let dragged: boolean,
		oldX: number,
		oldY: number;

	function docScrollLeft() {
		return (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
	}
	function docScrollTop() {
		return (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
	}

	function mouseX(e: MouseEvent) {
		if (e.pageX) {
			return e.pageX;
		}

		if (e.clientX) {
			return e.clientX + docScrollLeft();
		}

		return 0;
	}

    /**
     * @param {MouseEvent} e
     * @returns {number|null}
     */
	function mouseY(e: MouseEvent) {
		if (e.pageY) {
			return e.pageY;
		}

		if (e.clientY) {
			return e.clientY + docScrollTop();
		}

		return 0;
	}

    /**
     * @param {MouseEvent} e
     */
    function mouseMoveHandler(e: MouseEvent) {
        if (!dragObject.el) {
            return;
        }
        let instance = dragObject.el;
        e = e || window.event;
        let newX = mouseX(e);
        let newY = mouseY(e);
        dragged = instance.hasClass('savepos');
        let scrollTop = instance.scrollTop() || 0,
			scrollLeft = instance.scrollLeft() || 0;
        if (instance.hasClass('dragvertical')) {
            instance.scrollTop(scrollTop + oldY - newY);
        }
        else if (instance.hasClass('draghorizontal')) {
            instance.scrollLeft(scrollLeft + oldX - newX);
        }
        else {
        	let offset = instance.offset() || {left: 0, top: 0};

            instance.css({
                'top': (offset.top - docScrollTop() + newY - oldY) + 'px',
                'left': (offset.left - docScrollLeft() + newX - oldX) + 'px'
            });
        }
        oldX = newX;
        oldY = newY;
    }

    /**
     * @param {MouseEvent} e
     */
	function startDrag(e: DragEvent) {
		e = e || window.event;

		let target = e.target;

		if (target instanceof Element) {
            let tag = target.tagName.toUpperCase();
            let overflow = $(target).css('overflow');
            if ((tag !== 'DIV' && tag !== 'H1') || overflow === 'auto' || overflow === 'scroll') { // sliding scrollbar of dropdown menu or input field
                return;
            }
            dragObject.el = $(target);
            if (typeof dragObject.el === 'undefined' || !dragObject.el.hasClass('dragobject')) {
                dragObject.el = $(target).closest('.dragobject');
            }
            if (typeof dragObject.el !== 'undefined') {
                oldX = mouseX(e);
                oldY = mouseY(e);
                window.addEventListener('mousemove', mouseMoveHandler, true);
            }
            else {
                dragObject.el = undefined;
            }
        }
		dragged = false;
	}

	function stopDrag() {
		if (!dragObject.el) {
			return;
		}
		window.removeEventListener('mousemove', mouseMoveHandler, true);
		if (dragged) {
			let instance = dragObject.el;
			let top, left;
			if (instance.hasClass('dragvertical') || instance.hasClass('draghorizontal')) {
				top = instance.scrollTop();
				left = instance.scrollLeft();
			}
			else {
				let offset = instance.offset() || {top: 0, left: 0};
				top = offset.top - docScrollTop();
				left = offset.left - docScrollLeft();
			}
			$.post('/tools/dragobject.php', {
				id: instance.attr('id'),
				coords: {
					top,
					left
				}
			});
			dragged = false;
		}
        dragObject.el = undefined;
	}

	window.addEventListener('mousedown', startDrag, false);
	window.addEventListener('mouseup', stopDrag, false);
});

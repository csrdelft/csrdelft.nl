/**
 * dragobject.js  |  P.W.G. Brussee (brussee@live.nl)
 */
import axios from 'axios';

export const dragObject: { el: HTMLElement | null; } = {
	el: null,
};

let dragged: boolean;
let oldX: number;
let oldY: number;

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
	const instance = dragObject.el;
	const newX = mouseX(e);
	const newY = mouseY(e);
	dragged = instance.classList.contains('savepos');
	const scrollTop = instance.scrollTop
	const scrollLeft = instance.scrollLeft
	if (instance.classList.contains('dragvertical')) {
		instance.scrollTop = scrollTop + oldY - newY;
	} else if (instance.classList.contains('draghorizontal')) {
		instance.scrollLeft = scrollLeft + oldX - newX;
	} else {
		const offset = {left: instance.offsetLeft, top: instance.offsetTop};

		instance.style.left = (offset.left - docScrollLeft() + newX - oldX) + 'px'
		instance.style.top = (offset.top - docScrollTop() + newY - oldY) + 'px'
	}
	oldX = newX;
	oldY = newY;
}

/**
 * @param {MouseEvent} e
 */
function startDrag(e: MouseEvent) {

	const target = e.target;

	if (target instanceof HTMLElement) {
		const tag = target.tagName.toUpperCase();
		const overflow = getComputedStyle(target).overflow
		// sliding scrollbar of dropdown menu or input field
		if ((tag !== 'DIV' && tag !== 'H1') || overflow === 'auto' || overflow === 'scroll') {
			return;
		}
		dragObject.el = target;
		if (typeof dragObject.el === 'undefined' || !dragObject.el.classList.contains('dragobject')) {
			dragObject.el = target.closest('.dragobject');
		}
		if (typeof dragObject.el !== 'undefined') {
			oldX = mouseX(e);
			oldY = mouseY(e);
			window.addEventListener('mousemove', mouseMoveHandler, true);
		} else {
			dragObject.el = null;
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
		const instance = dragObject.el;
		let top;
		let left;
		if (instance.classList.contains('dragvertical') || instance.classList.contains('draghorizontal')) {
			top = instance.scrollTop;
			left = instance.scrollLeft;
		} else {
			const offset = {top: instance.offsetTop, left: instance.offsetLeft}
			top = offset.top - docScrollTop();
			left = offset.left - docScrollLeft();
		}
		axios.post('/tools/dragobject', {
			coords: {left, top},
			id: instance.id,
		});
		dragged = false;
	}
	dragObject.el = null;
}

window.addEventListener('mousedown', startDrag, false);
window.addEventListener('mouseup', stopDrag, false);

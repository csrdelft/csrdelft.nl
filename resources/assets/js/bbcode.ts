import axios from 'axios';
import $ from 'jquery';
import './bbcode-hints';
import ctx, {init} from './ctx';
import {singleLineString} from './util';

/**
 * Preview button, update bbcode als op de knop geklikt wordt.
 */
ctx.addHandler('[data-bbpreview-btn]', (el: HTMLElement) => {
	const previewId = el.dataset!.bbpreviewBtn!;
	const source = document.querySelector<HTMLTextAreaElement>('#' + previewId);
	const target = document.querySelector<HTMLElement>('#preview_' + previewId);

	if (!source || !target) {
		throw new Error('Bbpreview van niet bestaande elementen');
	}

	el.addEventListener('click', () => CsrBBPreviewEl(source, target));
});
/**
 * Preview element, update bbcode als er op enter gedrukt wordt.
 */
ctx.addHandler('[data-bbpreview]', (el: HTMLTextAreaElement) => {
	const previewId = el.dataset!.bbpreview!;
	const target = document.querySelector<HTMLElement>('#preview_' + previewId);

	if (!target) {
		throw new Error('Geen target gevonden voor bbpreview');
	}

	el.addEventListener('keyup', (event) => {
		if (event.key === 'Enter') { // enter
			CsrBBPreviewEl(el, target);
		}
	});
});

export const CsrBBPreviewEl = (source: HTMLTextAreaElement, target: HTMLElement, params: object = {}) => {
	const bbcode = source.value;

	if (bbcode.trim() === '') {
		target.innerHTML = '';
		target.style.display = 'none';
		return;
	}

	axios.post('/tools/bbcode', {
		data: encodeURIComponent(bbcode),
		...params,
	}).then((response) => {
		target.innerHTML = response.data;
		init(target);
		target.style.display = 'block';
	}).catch((error) => {
		alert(error);
	});
};

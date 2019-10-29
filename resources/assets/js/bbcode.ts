import axios from 'axios';
import $ from 'jquery';
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

/**
 * @see view/bbcode/CsrBB.class.php
 */
export const bbvideoDisplay = (elem: string) => {
	const $previewdiv = $(elem);
	const params = $previewdiv.data('params');
	if (params.iframe) {
		// vervang de thumb door een iframe
		$previewdiv
			.parent()
			.html(singleLineString`
<iframe
	frameborder="0"
	width="${params.width}"
	height="${params.height}"
	src="${params.src}"
	allowfullscreen></iframe>`);
	} else {
		// godtube is nog flash (of alternatief iets met javascript, maar geen iframe versie beschikbaar)
		$previewdiv.parent().html(singleLineString`
<object
	type="application/x-shockwave-flash"
	data="http://www.godtube.com/resource/mediaplayer/5.3/player.swf"
	width="${params.width}"
	height="${params.height}">
		<param name="allowfullscreen" value="true" />
		<param name="allowscriptaccess" value="always" />
		<param name="wmode" value="opaque" />
		<param name="movie" value="http://www.godtube.com/resource/mediaplayer/5.3/player.swf" />
		<param name="autostart" value="true" />
		<param name="flashvars" value="file=http://www.godtube.com/resource/mediaplayer/${params.id}.file
						&image=http://www.godtube.com/resource/mediaplayer/${params.id}.jpg
						&screencolor=000000
						&type=video
						&autostart=true
						&playonce=true
						&skin=http://www.godtube.com//resource/mediaplayer/skin/carbon/carbon.zip
						&logo.file=http://media.salemwebnetwork.com/godtube/theme/default/media/embed-logo.png
						&logo.link=http://www.godtube.com/watch/?v=${params.id}
						&logo.position=top-left
						&logo.hide=false
						&controlbar.position=over"></object>`);
	}

	return false;
};

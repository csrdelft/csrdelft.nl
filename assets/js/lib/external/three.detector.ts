/**
 * @author alteredq / http://alteredqualia.com/
 * @author mr.doob / http://mrdoob.com/
 */

export default {

	canvas: typeof (CanvasRenderingContext2D) !== 'undefined',
	webgl: (() => {
		try {
			return typeof (WebGLRenderingContext) !== 'undefined' && !!document.createElement('canvas').getContext('experimental-webgl');
		} catch (e) {
			return false;
		}
	})(),
	workers: typeof (Worker) !== 'undefined',
	fileapi: typeof (File) !== 'undefined'
		&& typeof (FileReader) !== 'undefined'
		&& typeof (FileList) !== 'undefined'
		&& typeof (Blob) !== 'undefined',

	getWebGLErrorMessage(): HTMLDivElement {
		const domElement = document.createElement('div');

		domElement.style.fontFamily = 'monospace';
		domElement.style.fontSize = '13px';
		domElement.style.textAlign = 'center';
		domElement.style.background = '#eee';
		domElement.style.color = '#000';
		domElement.style.padding = '1em';
		domElement.style.width = '475px';
		domElement.style.margin = '5em auto 0';

		if (!this.webgl) {
			domElement.innerHTML = typeof (WebGLRenderingContext) !== 'undefined' ? [
				'Sorry, your graphics card doesn\'t support <a href="http://khronos.org/webgl/wiki/Getting_a_WebGL_Implementation">WebGL</a>',
			].join('\n') : [
				'Sorry, your browser doesn\'t support <a href="http://khronos.org/webgl/wiki/Getting_a_WebGL_Implementation">WebGL</a><br/>',
				'Please try with',
				'<a href="http://www.google.com/chrome">Chrome 10</a>, ',
				'<a href="http://www.mozilla.com/en-US/firefox/all-beta.html">Firefox 4</a> or',
				'<a href="http://nightly.webkit.org/">Safari 6</a>',
			].join('\n');
		}
		return domElement;
	},

	addGetWebGLMessage(): void {
		const domElement = this.getWebGLErrorMessage();
		domElement.id = 'oldie';
		document.body.appendChild(domElement);
	},
};

import {domUpdate} from './context';

/**
 * Selecteer de tekst van een DOM-element.
 * @source http://stackoverflow.com/questions/985272/
 *
 * @see templates/fotoalbum/album.tpl
 */
export function selectText(elmnt: HTMLElement) {
	const selection = window.getSelection()!;
	const range = document.createRange();
	range.selectNodeContents(elmnt);
	selection.removeAllRanges();
	selection.addRange(range);
}

/**
 *  discuss at: http://phpjs.org/functions/dirname/
 * original by: Ozh
 * improved by: XoraX (http://www.xorax.info)
 *   example 1: dirname('/etc/passwd');
 *   returns 1: '/etc'
 *   example 2: dirname('c:/Temp/x');
 *   returns 2: 'c:/Temp'
 *   example 3: dirname('/dir/test/');
 *   returns 3: '/dir'
 */
export function dirname(path: string) {
	return path.replace(/\\/g, '/')
		.replace(/\/[^/]*\/?$/, '');
}

export function basename(path: string, suffix: string = '') {
	//  discuss at: http://phpjs.org/functions/basename/
	// original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// improved by: Ash Searle (http://hexmen.com/blog/)
	// improved by: Lincoln Ramsay
	// improved by: djmix
	// improved by: Dmitry Gorelenkov
	//   example 1: basename('/www/site/home.htm', '.htm');
	//   returns 1: 'home'
	//   example 2: basename('ecra.php?p=1');
	//   returns 2: 'ecra.php?p=1'
	//   example 3: basename('/some/path/');
	//   returns 3: 'path'
	//   example 4: basename('/some/path_ext.ext/','.ext');
	//   returns 4: 'path_ext'

	let base = path;
	const lastChar = base.charAt(base.length - 1);

	if (lastChar === '/' || lastChar === '\\') {
		base = base.slice(0, -1);
	}

	base = base.replace(/^.*[/\\]/g, '');

	if (suffix !== '' && base.substr(base.length - suffix.length) === suffix) {
		base = base.substr(0, base.length - suffix.length);
	}

	return base;
}

export function reload(htmlString: string | object | boolean) {
	if (typeof htmlString === 'string' && htmlString.substring(0, 16) === '<div id="modal" ') {
		domUpdate(htmlString);
		return;
	}
	location.reload();
}

export function redirect(htmlString: string) {
	if (htmlString.substring(0, 16) === '<div id="modal" ') {
		domUpdate(htmlString);
		return;
	}
	window.location.href = htmlString;
}

export function route(path: string, cb: () => void) {
	if (window.location.pathname.startsWith(path)) {
		cb();
	}
}

/**
 * Verwerk een multipliciteit in de vorm van `== 1` of `!= 0` of `> 3` voor de selecties
 */
export function evaluateMultiplicity(expression: string, num: number): boolean {
	// Altijd laten zien bij geen expressie
	if (expression.length === 0) {
		return true;
	}

	const [expressionOperator, expressionAantalString] = expression.split(' ');

	const expressionAantal = parseInt(expressionAantalString, 10);

	const mapOperationToFunction: { [op: string]: (a: number, b: number) => boolean } = {
		'!=': (a, b) => a !== b,
		'<': (a, b) => a < b,
		'<=': (a, b) => a <= b,
		'==': (a, b) => a === b,
		'>': (a, b) => a > b,
		'>=': (a, b) => a >= b,
	};

	return mapOperationToFunction[expressionOperator](num, expressionAantal);
}

export function formatFilesize(data: string) {
	const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	let i = 0;
	let size = Number(data);
	while (size >= 1024) {
		size /= 1024;
		++i;
	}
	return size.toFixed(1) + ' ' + units[i];
}

export function formatBedrag(data: number) {
	if (data > 0) {
		return '€' + (data / 100).toFixed(2);
	} else {
		return '-€' + (data / -100).toFixed(2);
	}
}

export function singleLineString(strings: TemplateStringsArray, ...values: string[]) {
	// Interweave the strings with the
	// substitution vars first.
	let output = '';
	for (let i = 0; i < values.length; i++) {
		output += strings[i] + values[i];
	}
	output += strings[values.length];

	// Split on newlines.
	const lines = output.split(/(?:\r\n|\n|\r)/);

	// Rip out the leading whitespace.
	return lines.map((line) => line.replace(/^\s+/gm, '')).join(' ').trim();
}

export function html(strings: TemplateStringsArray, ...values: Array<string | undefined>): HTMLElement {
	let output = '';
	for (let i = 0; i < values.length; i++) {
		output += strings[i] + values[i];
	}
	output += strings[values.length];

	return (new DOMParser().parseFromString(output, 'text/html').body.firstChild) as HTMLElement;
}

export function htmlParse(htmlString: string) {
	return jQuery.parseHTML(htmlString, null, true) as Node[];
}

export function preloadImage(url: string, callback: () => void) {
	const img = new Image();
	img.src = url;
	img.onload = callback;
}

export function parseData(el: HTMLElement) {
	const data = el.dataset;

	const out: any = {};

	for (const item of Object.keys(data)) {
		if (data[item] === 'false') {
			out[item] = false;
		} else if (data[item] === 'true') {
			out[item] = true;
		} else if (!isNaN(Number(data[item]))) {
			out[item] = Number(data[item]);
		} else {
			out[item] = data[item];
		}
	}

	return out;
}

export function htmlEncode(str: string) {
	return String(str)
		.replace(/&/g, '&amp;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;');
}

export function ontstuiter(func: any, wait: number, immediate: boolean) {
	let timeout: number | undefined;
	return function (this: any) {
		const context = this;
		const args = arguments;
		const later = () => {
			timeout = undefined;
			if (!immediate) {
				func.apply(context, args);
			}
		};
		const callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = window.setTimeout(later, wait);
		if (callNow) {
			func.apply(context, args);
		}
	};
}

export function docReady(fn: () => void): void {
	if (document.readyState === 'complete') {
		fn();
	} else {
		document.addEventListener('DOMContentLoaded', fn);
	}
}

export function isLoggedIn(): boolean {
	const elem = document.querySelector('meta[property=\'X-CSR-LOGGEDIN\']');
	if (!elem) {
		return false;
	}
	return elem.getAttribute('value') === 'true';
}

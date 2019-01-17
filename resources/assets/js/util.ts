import {domUpdate} from './context';

/**
 * Selecteer de tekst van een DOM-element.
 * @source http://stackoverflow.com/questions/985272/jquery-selecting-text-in-an-element-akin-to-highlighting-with-your-mouse/987376#987376
 *
 * @see templates/fotoalbum/album.tpl
 */
export function selectText(elmnt: HTMLElement) {
	let range, selection;
	selection = window.getSelection();
	range = document.createRange();
	range.selectNodeContents(elmnt);
	selection.removeAllRanges();
	selection.addRange(range);
}


/**
 //  discuss at: http://phpjs.org/functions/dirname/
 // original by: Ozh
 // improved by: XoraX (http://www.xorax.info)
 //   example 1: dirname('/etc/passwd');
 //   returns 1: '/etc'
 //   example 2: dirname('c:/Temp/x');
 //   returns 2: 'c:/Temp'
 //   example 3: dirname('/dir/test/');
 //   returns 3: '/dir'
 */
export function dirname(path : string) {
	return path.replace(/\\/g, '/')
		.replace(/\/[^/]*\/?$/, '');
}

export function basename(path : string, suffix : string = '') {
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
	let lastChar = base.charAt(base.length - 1);

	if (lastChar === '/' || lastChar === '\\') {
		base = base.slice(0, -1);
	}

	base = base.replace(/^.*[/\\]/g, '');

	if (suffix !== '' && base.substr(base.length - suffix.length) === suffix) {
		base = base.substr(0, base.length - suffix.length);
	}

	return base;
}

export function reload(htmlString? : string) {
	if (htmlString && htmlString.substring(0, 16) === '<div id="modal" ') {
		domUpdate(htmlString);
		return;
	}
	location.reload();
}

export function redirect(htmlString : string) {
	if (htmlString.substring(0, 16) === '<div id="modal" ') {
		domUpdate(htmlString);
		return;
	}
	window.location.href = htmlString;
}

export function route(path : string, cb : Function) {
	if (window.location.pathname.startsWith(path)) {
		cb();
	}
}

/**
 * Verwerk een multipliciteit in de vorm van `== 1` of `!= 0` of `> 3` voor de selecties
 */
export function evaluateMultiplicity(expression : string, num : number): boolean {
	// Altijd laten zien bij geen expressie
	if (expression.length === 0) {
		return true;
	}

	let [expressionOperator, expressionAantalString] = expression.split(' ');

	let expressionAantal = parseInt(expressionAantalString);

	let mapOperationToFunction : {[op: string]: (a: number, b: number) => boolean} = {
		'==': (a, b) => a === b,
		'!=': (a, b) => a !== b,
		'>=': (a, b) => a >= b,
		'>': (a, b) => a > b,
		'<=': (a, b) => a <= b,
		'<': (a, b) => a < b
	};

	return mapOperationToFunction[expressionOperator](num, expressionAantal);
}

export function formatFilesize(data: string) {
	let units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	let i = 0;
	let size = Number(data);
	while (size >= 1024) {
		size /= 1024;
		++i;
	}
	return size.toFixed(1) + ' ' + units[i];
}

export function formatBedrag(data : number) {
	if (data > 0) {
		return '€' + (data / 100).toFixed(2);
	} else {
		return '-€' + (data / -100).toFixed(2);
	}
}

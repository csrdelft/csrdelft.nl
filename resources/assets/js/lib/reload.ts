import {domUpdate} from './domUpdate';

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

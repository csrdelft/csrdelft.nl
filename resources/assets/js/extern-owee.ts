import './ajax-csrf';
import './bootstrap';
import {docReady} from './util';

declare global {
	interface Window {
		$: JQueryStatic;
		jQuery: JQueryStatic;
		docReady: (fn: () => void) => void;
	}
}

window.docReady = docReady;

docReady(() => {
	console.log('loaded');
});

import { render, register } from 'timeago.js';

import nl from 'timeago.js/lib/lang/nl';

register('nl', nl);

export const initTimeago = (el: HTMLElement) => {
	render(el, 'nl');
};

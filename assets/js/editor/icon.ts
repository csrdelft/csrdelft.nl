import { html } from '../lib/util';

export default {
	strong: { dom: html`<i class="fas fa-bold" aria-hidden="true"></i>` },
	em: { dom: html`<i class="fas fa-italic" aria-hidden="true"></i>` },
	link: { dom: html`<i class="fas fa-link" aria-hidden="true"></i>` },
	undo: { dom: html`<i class="fas fa-undo" aria-hidden="true"></i>` },
	redo: { dom: html`<i class="fas fa-redo" aria-hidden="true"></i>` },
	ol: { dom: html`<i class="fas fa-list-ol" aria-hidden="true"></i>` },
	ul: { dom: html`<i class="fas fa-list-ul" aria-hidden="true"></i>` },
	quote: { dom: html`<i class="fas fa-quote-left" aria-hidden="true"></i>` },
	underline: { dom: html`<i class="fas fa-underline" aria-hidden="true"></i>` },
	selectParentNode: {
		dom: html`<i class="fas fa-object-group" aria-hidden="true"></i>`,
	},
	lift: { dom: html`<i class="fas fa-outdent" aria-hidden="true"></i>` },
	prive: { dom: html`<i class="fas fa-user-shield" aria-hidden="true"></i>` },
	join: { dom: html`<i class="fas fa-caret-up" aria-hidden="true"></i>` },
};

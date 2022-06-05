import { html } from '../lib/util';

export default {
	strong: { dom: html`<i class="fas fa-bold"></i>` },
	em: { dom: html`<i class="fas fa-italic"></i>` },
	link: { dom: html`<i class="fas fa-link"></i>` },
	undo: { dom: html`<i class="fas fa-undo"></i>` },
	redo: { dom: html`<i class="fas fa-redo"></i>` },
	ol: { dom: html`<i class="fas fa-list-ol"></i>` },
	ul: { dom: html`<i class="fas fa-list-ul"></i>` },
	quote: { dom: html`<i class="fas fa-quote-right"></i>` },
	underline: { dom: html`<i class="fas fa-underline"></i>` },
	selectParentNode: { dom: html`<i class="fas fa-object-group"></i>` },
	lift: { dom: html`<i class="fas fa-outdent"></i>` },
	prive: { dom: html`<i class="fas fa-user-shield"></i>` },
	join: { dom: html`<i class="fas fa-caret-up"></i>` },
};

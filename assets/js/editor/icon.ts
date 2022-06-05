import { html } from '../lib/util';

export default {
	strong: { dom: html`<i class="fa fa-bold"></i>` },
	em: { dom: html`<i class="fa fa-italic"></i>` },
	link: { dom: html`<i class="fa fa-link"></i>` },
	undo: { dom: html`<i class="fa fa-undo"></i>` },
	redo: { dom: html`<i class="fa fa-redo"></i>` },
	ol: { dom: html`<i class="fa fa-list-ol"></i>` },
	ul: { dom: html`<i class="fa fa-list-ul"></i>` },
	quote: { dom: html`<i class="fa fa-quote-right"></i>` },
	underline: { dom: html`<i class="fa fa-underline"></i>` },
	selectParentNode: { dom: html`<i class="fa fa-object-group"></i>` },
	lift: { dom: html`<i class="fa fa-outdent"></i>` },
	prive: { dom: html`<i class="fa fa-user-shield"></i>` },
	join: { dom: html`<i class="fa fa-caret-up"></i>` },
};

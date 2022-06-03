import { html } from '../lib/util';
import Bloodhound from 'corejs-typeahead';
import getVideoId from 'get-video-id';

const prefix = 'ProseMirror-popup';

interface PromptOptions<T> {
	title: string;
	description?: string;
	fields: Record<string, Field<unknown>>;
	callback: (params: T) => void;
}

export function openPrompt<T = any>(options: PromptOptions<T>): void {
	const submitButton = html` <button type="submit" class="${prefix}-submit btn btn-primary">OK</button>`;
	const cancelButton = html` <button type="button" class="${prefix}-cancel btn btn-primary">Cancel</button>`;

	const form = document.createElement('form');
	const formBody = form.appendChild(html` <div class="modal-body"></div>`);
	const formFooter = form.appendChild(html` <div class="modal-footer"></div>`);

	if (options.description) {
		formBody.appendChild(html`<p>${options.description}</p>`);
	}

	Object.entries(options.fields).forEach(([name, field]) => {
		// prettier-ignore
		formBody.appendChild(html`<div class="mb-3 row">
<label class="col-sm-2 col-form-label" for="${name}"
>${field.options.label}${field.options.required ? html`<span class="text-danger">*</span>` : ''}</label
>
<div class="col-sm-10">${field.render(name)}</div>
</div>`);
	});

	formFooter.appendChild(html` <div class="${prefix}-buttons">${submitButton} ${cancelButton}</div>`);

	// prettier-ignore
	const modal = html`<div class="modal" style="display: block;" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">${options.title}</h5>
</div>
${form}
</div>
</div>
</div>`;

	document.body.appendChild(modal);

	const mouseOutside = (e) => {
		if (!form.contains(e.target)) close();
	};
	setTimeout(() => window.addEventListener('mousedown', mouseOutside), 50);
	const close = () => {
		window.removeEventListener('mousedown', mouseOutside);
		if (modal.parentNode) modal.parentNode.removeChild(modal);
	};

	cancelButton.addEventListener('click', close);

	const submit = () => {
		const params = getValues(options.fields, form);
		if (params) {
			close();
			options.callback(params);
		}
	};

	form.addEventListener('submit', (e) => {
		e.preventDefault();
		submit();
	});

	form.addEventListener('keydown', (e) => {
		if (e.key == 'Escape') {
			e.preventDefault();
			close();
		} else if (
			e.key == 'Enter' &&
			!(e.ctrlKey || e.metaKey || e.shiftKey) &&
			document.activeElement.tagName.toLowerCase() != 'textarea'
		) {
			e.preventDefault();
			submit();
		} else if (e.key == 'Tab') {
			window.setTimeout(() => {
				if (!modal.contains(document.activeElement)) close();
			}, 500);
		}
	});

	const input = form.elements[0] as HTMLInputElement;
	if (input) input.focus();
}

function getValues<T>(fields: Record<string, Field<T>>, form: HTMLFormElement) {
	const result = Object.create(null);
	for (const name of Object.keys(fields)) {
		const field = fields[name];
		const dom = form[name];
		const value = field.read(dom);
		const bad = field.validate(value);
		if (bad) {
			reportInvalid(dom, bad);
			return null;
		}
		result[name] = field.clean(value);
	}
	return result;
}

function reportInvalid(dom, message) {
	// FIXME this is awful and needs a lot more work
	const parent = dom.parentNode;
	const msg = parent.appendChild(document.createElement('div'));
	msg.style.left = dom.offsetLeft + dom.offsetWidth + 2 + 'px';
	msg.style.top = dom.offsetTop - 5 + 'px';
	msg.className = 'ProseMirror-invalid';
	msg.textContent = message;
	setTimeout(() => parent.removeChild(msg), 1500);
}

interface FieldOptions<T = unknown> {
	value?: T;
	label: string;
	required?: boolean;
	validate?: (val: T) => string;
	clean?: (val: T) => T;
	options?: FieldOptions<T>[];
}

// ::- The type of field that `FieldPrompt` expects to be passed to it.
export class Field<T = string> {
	options: FieldOptions<T>;
	// :: (Object)
	// Create a field with the given options. Options support by all
	// field types are:
	//
	// **`value`**`: ?any`
	//   : The starting value for the field.
	//
	// **`label`**`: string`
	//   : The label for the field.
	//
	// **`required`**`: ?bool`
	//   : Whether the field is required.
	//
	// **`validate`**`: ?(any) → ?string`
	//   : A function to validate the given value. Should return an
	//     error message if it is not valid.
	constructor(options: FieldOptions<T>) {
		this.options = options;
	}

	// render:: (state: EditorState, props: Object) → dom.Node
	// Render the field to the DOM. Should be implemented by all subclasses.

	// :: (dom.Node) → any
	// Read the field's value from its DOM node.
	read(dom: HTMLInputElement): T {
		return dom.value as unknown as T;
	}

	// :: (any) → ?string
	// A field-type-specific validation function.
	validateType(_value: T): string {
		return null;
	}

	validate(value: T): string {
		if (!value && this.options.required) return 'Required field';
		return this.validateType(value) || (this.options.validate && this.options.validate(value));
	}

	clean(value: T): T {
		return this.options.clean ? this.options.clean(value) : value;
	}

	render(name: string): HTMLElement {
		// abstract
		return null;
	}
}

// ::- A field class for single-line text fields.
export class TextField extends Field {
	render(name: string): HTMLElement {
		// prettier-ignore
		return html`<input
type="text"
name="${name}"
id="${name}"
value="${this.options.value || ''}"
autocomplete="off"
class="form-control"
/>`;
	}
}

export class Label extends Field {
	render(name: string): HTMLElement {
		return html`<div>${this.options.value}</div>`;
	}

	read(dom: HTMLInputElement): string {
		return '';
	}
}

// ::- A field class for dropdown fields based on a plain `<select>`
// tag. Expects an option `options`, which should be an array of
// `{value: string, label: string}` objects, or a function taking a
// `ProseMirror` instance and returning such an array.
export class SelectField extends Field {
	render(name: string): HTMLElement {
		const select = document.createElement('select');
		select.id = name;
		select.name = name;
		this.options.options.forEach((o) => {
			const opt = select.appendChild(document.createElement('option'));
			opt.value = o.value;
			opt.selected = o.value == this.options.value;
			opt.label = o.label;
		});
		return select;
	}
}

export class TextAreaField extends Field {
	render(name: string): HTMLElement {
		const input = document.createElement('textarea');
		input.id = name;
		input.name = name;
		input.classList.add('form-control');
		input.placeholder = this.options.label;
		input.value = this.options.value || '';
		input.autocomplete = 'off';
		input.rows = 20;
		return input;
	}
}

export class FileField extends Field<File> {
	render(name: string): HTMLElement {
		const input = document.createElement('input');
		input.id = name;
		input.name = name;
		input.classList.add('form-control');
		input.type = 'file';

		return input;
	}

	read(dom: HTMLInputElement): File {
		return dom.files[0];
	}
}

export class LidField extends Field<{ uid: string; naam: string }> {
	name: string;

	render(name: string): HTMLElement {
		this.name = name;
		// prettier-ignore
		const textInput = html`<input
type="text"
class="form-control"
autocomplete="off"
name="${name}_naam"
value="${this.options.value.naam}"
/>`;
		// prettier-ignore
		const hiddenInput = html<HTMLInputElement>`<input
type="hidden"
name="${name}_uid"
value="${this.options.value.uid}"
/>`;
		const auxInput = html`<input type="hidden" name="${name}" />`;

		const ledenDataset = new Bloodhound({
			datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			remote: {
				url: '/tools/naamsuggesties?vorm=user&zoekin=voorkeur&q=%QUERY',
				wildcard: '%QUERY',
			},
		});

		ledenDataset.initialize();

		let textValue = this.options.value.naam;

		setTimeout(() => {
			$(textInput).typeahead(
				{
					hint: true,
					highlight: true,
				},
				{
					name: 'leden',
					display: 'value',
					limit: 20,
					source: ledenDataset.ttAdapter(),
					templates: {
						suggestion: function (suggestion) {
							let html = '<p';
							if (suggestion.title) {
								html += ' title="' + suggestion.title + '"';
							}
							html += '>';
							if (suggestion.icon) {
								html += suggestion.icon;
							}
							html += suggestion.value;
							if (suggestion.label) {
								html += '<span class="lichtgrijs"> - ' + suggestion.label + '</span>';
							}
							return html + '</p>';
						},
					},
				}
			);

			$(textInput).on('typeahead:select', (event, suggestion) => {
				hiddenInput.value = suggestion['uid'];
				textValue = suggestion['value'];
			});

			$(textInput).on('typeahead:change', (event, value) => {
				if (textValue != value) {
					hiddenInput.value = '';
				}
			});
		});

		return html` <div>${textInput} ${hiddenInput} ${auxInput}</div>`;
	}

	read(dom: HTMLInputElement): { uid: string; naam: string } {
		return {
			naam: dom.form[`${this.name}_naam`].value,
			uid: dom.form[`${this.name}_uid`].value,
		};
	}

	validate(value: { uid: string; naam: string }): string {
		if (this.options.required && !value.uid) {
			return 'Selecteer een lid';
		}

		return super.validate(value);
	}
}

export class YoutubeField extends Field<string> {
	render(name: string): HTMLElement {
		// prettier-ignore
		return html`<textarea name="${name}" id="${name}" autocomplete="off" class="form-control">
${this.options.value || ''}
</textarea>`;
	}

	read(dom: HTMLInputElement): string {
		const videoId = getVideoId(dom.value);
		if (videoId.service != 'youtube') {
			return '';
		}

		return videoId.id;
	}
}

// From "prosemirror-example-setup/src/prompt"
import {html} from "../lib/util";

const prefix = "ProseMirror-popup"

interface PromptOptions {
	title: string
	fields: Record<string, Field<any, any>>
	callback: (params: any) => void
}

export function openPrompt(options: PromptOptions): void {
	const submitButton = html`
		<button type="submit" class="${prefix}-submit btn btn-primary">OK</button>`
	const cancelButton = html`
		<button type="button" class="${prefix}-cancel btn btn-primary">Cancel</button>`

	const form = document.createElement("form")
	const formBody = form.appendChild(html`
		<div class="modal-body"></div>`)
	const formFooter = form.appendChild(html`
		<div class="modal-footer"></div>`)

	Object.entries(options.fields).forEach(([name, field]) => {
		formBody.appendChild(html`
			<div class="form-group row">
				<label class="col-sm-2 col-form-label">${field.options.label}</label>
				<div class="col-sm-10">${field.render(name)}</div>
			</div>`)
	})

	formFooter.appendChild(html`
		<div class="${prefix}-buttons">${submitButton} ${cancelButton}</div>`)

	const modal = html`
		<div class="modal" style="display: block;" tabindex="-1">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">${options.title}</h5>
					</div>
					${form}
				</div>
			</div>
		</div>`

	document.body.appendChild(modal)

	const mouseOutside = e => {
		if (!form.contains(e.target)) close()
	}
	setTimeout(() => window.addEventListener("mousedown", mouseOutside), 50)
	const close = () => {
		window.removeEventListener("mousedown", mouseOutside)
		if (modal.parentNode) modal.parentNode.removeChild(modal)
	}

	cancelButton.addEventListener("click", close)

	const submit = () => {
		const params = getValues(options.fields, form)
		if (params) {
			close()
			options.callback(params)
		}
	}

	form.addEventListener("submit", e => {
		e.preventDefault()
		submit()
	})

	form.addEventListener("keydown", e => {
		if (e.key == "Escape") {
			e.preventDefault()
			close()
		} else if (e.key == "Enter" && !(e.ctrlKey || e.metaKey || e.shiftKey) && document.activeElement.tagName.toLowerCase() != "textarea") {
			e.preventDefault()
			submit()
		} else if (e.key == "Tab") {
			window.setTimeout(() => {
				if (!modal.contains(document.activeElement)) close()
			}, 500)
		}
	})

	const input = form.elements[0] as HTMLInputElement
	if (input) input.focus()
}

function getValues(fields: Record<string, Field<string, any>>, form: HTMLFormElement) {
	const result = Object.create(null)
	for (const name of Object.keys(fields)) {
		const field = fields[name]
		const dom = form[name]
		const value = field.read(dom)
		const bad = field.validate(value)
		if (bad) {
			reportInvalid(dom, bad)
			return null
		}
		result[name] = field.clean(value)
	}
	return result
}

function reportInvalid(dom, message) {
	// FIXME this is awful and needs a lot more work
	const parent = dom.parentNode
	const msg = parent.appendChild(document.createElement("div"))
	msg.style.left = (dom.offsetLeft + dom.offsetWidth + 2) + "px"
	msg.style.top = (dom.offsetTop - 5) + "px"
	msg.className = "ProseMirror-invalid"
	msg.textContent = message
	setTimeout(() => parent.removeChild(msg), 1500)
}

interface FieldOptions<T = unknown> {
	value?: T
	label: string
	required?: boolean
	validate?: (val: T) => string
	clean?: (val: T) => T
	options?: FieldOptions<T>[]
}

// ::- The type of field that `FieldPrompt` expects to be passed to it.
export class Field<T extends string, U = string> {
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
		this.options = options
	}

	// render:: (state: EditorState, props: Object) → dom.Node
	// Render the field to the DOM. Should be implemented by all subclasses.

	// :: (dom.Node) → any
	// Read the field's value from its DOM node.
	read(dom: HTMLInputElement): U {
		return dom.value as unknown as U
	}

	// :: (any) → ?string
	// A field-type-specific validation function.
	validateType(_value: T): string {
		return null
	}

	validate(value: T): string {
		if (!value && this.options.required)
			return "Required field"
		return this.validateType(value) || (this.options.validate && this.options.validate(value))
	}

	clean(value: T): T {
		return this.options.clean ? this.options.clean(value) : value
	}

	render(name: string): HTMLElement {
		// abstract
		return null
	}
}

// ::- A field class for single-line text fields.
export class TextField extends Field<any> {
	render(name: string) {
		return html`<input type="text" name="${name}" value="${this.options.value || ""}" autocomplete="off" class="form-control"/>`
	}
}

// ::- A field class for dropdown fields based on a plain `<select>`
// tag. Expects an option `options`, which should be an array of
// `{value: string, label: string}` objects, or a function taking a
// `ProseMirror` instance and returning such an array.
export class SelectField extends Field<any> {
	render(name: string) {
		const select = document.createElement("select")
		select.name = name
		this.options.options.forEach(o => {
			const opt = select.appendChild(document.createElement("option"))
			opt.value = o.value
			opt.selected = o.value == this.options.value
			opt.label = o.label
		})
		return select
	}
}

export class TextAreaField extends Field<any> {
	render(name: string) {
		const input = document.createElement("textarea")
		input.name = name
		input.classList.add("form-control")
		input.placeholder = this.options.label
		input.value = this.options.value || ""
		input.autocomplete = "off"
		return input
	}
}

export class FileField extends Field<string, File> {
	render(name: string) {
		const input = document.createElement("input")
		input.name = name
		input.classList.add("form-control")
		input.type = "file"

		return input
	}

	read(dom: HTMLInputElement): File {
		return dom.files[0]
	}
}

// From "prosemirror-example-setup/src/prompt"
import {html} from "../lib/util";

const prefix = "ProseMirror-popup"

interface PromptOptions {
	title: string
	fields: Record<string, Field<any>>
	callback: (params: any) => void
}

export function openPrompt(options: PromptOptions) {

	const domFields = []
	for (const name in options.fields) domFields.push(options.fields[name].render())

	const submitButton = document.createElement("button")
	submitButton.type = "submit"
	submitButton.className = prefix + "-submit btn btn-primary"
	submitButton.textContent = "OK"
	const cancelButton = document.createElement("button")
	cancelButton.type = "button"
	cancelButton.className = prefix + "-cancel btn btn-secondary"
	cancelButton.textContent = "Cancel"

	const form = document.createElement("form")
	const formBody = form.appendChild(document.createElement("div"))
	formBody.classList.add("modal-body")
	const formFooter = form.appendChild(document.createElement("div"))
	formFooter.classList.add("modal-footer")

	domFields.forEach(field => {
		formBody.appendChild(document.createElement("div")).appendChild(field)
	})
	const buttons = form.appendChild(document.createElement("div"))
	buttons.className = prefix + "-buttons"
	buttons.appendChild(submitButton)
	buttons.appendChild(document.createTextNode(" "))
	buttons.appendChild(cancelButton)

	formFooter.appendChild(buttons)

	const modal = html`<div class="modal" style="display: block;" tabindex="-1">
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

	const mouseOutside = e => { if (!form.contains(e.target)) close() }
	setTimeout(() => window.addEventListener("mousedown", mouseOutside), 50)
	const close = () => {
		window.removeEventListener("mousedown", mouseOutside)
		if (modal.parentNode) modal.parentNode.removeChild(modal)
	}

	cancelButton.addEventListener("click", close)

	const submit = () => {
		const params = getValues(options.fields, domFields)
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
		if (e.keyCode == 27) {
			e.preventDefault()
			close()
		} else if (e.keyCode == 13 && !(e.ctrlKey || e.metaKey || e.shiftKey) && document.activeElement.tagName.toLowerCase() != "textarea") {
			console.log(document.activeElement.tagName)
			e.preventDefault()
			submit()
		} else if (e.keyCode == 9) {
			window.setTimeout(() => {
				if (!modal.contains(document.activeElement)) close()
			}, 500)
		}
	})

	const input = form.elements[0] as HTMLInputElement
	if (input) input.focus()
}

function getValues(fields, domFields) {
	const result = Object.create(null)
	let i = 0
	for (const name in fields) {
		const field = fields[name], dom = domFields[i++]
		const value = field.read(dom), bad = field.validate(value)
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
	value: T
	label: string
	required?: boolean
	validate?: (val: T) => string
	clean?: (val: T) => T
	options?: FieldOptions<T>[]
}

// ::- The type of field that `FieldPrompt` expects to be passed to it.
export class Field<T extends string> {
	protected options: FieldOptions<T>;
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
	constructor(options: FieldOptions<T>) { this.options = options }

	// render:: (state: EditorState, props: Object) → dom.Node
	// Render the field to the DOM. Should be implemented by all subclasses.

	// :: (dom.Node) → any
	// Read the field's value from its DOM node.
	read(dom: HTMLInputElement) { return dom.value }

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

	render() {
		// abstract
	}
}

// ::- A field class for single-line text fields.
export class TextField extends Field<any> {
	render() {
		const input = document.createElement("input")
		input.type = "text"
		input.placeholder = this.options.label
		input.value = this.options.value || ""
		input.autocomplete = "off"
		return input
	}
}


// ::- A field class for dropdown fields based on a plain `<select>`
// tag. Expects an option `options`, which should be an array of
// `{value: string, label: string}` objects, or a function taking a
// `ProseMirror` instance and returning such an array.
export class SelectField extends Field<any> {
	render() {
		const select = document.createElement("select")
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
	render() {
		const input = document.createElement("textarea")
		input.classList.add("form-control")
		input.placeholder = this.options.label
		input.value = this.options.value || ""
		input.autocomplete = "off"
		return input
	}
}

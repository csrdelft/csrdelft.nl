import {MarkSpec, Schema} from "prosemirror-model";
import {icons, MenuItem} from "prosemirror-menu";
import {toggleMark} from "prosemirror-commands";
import {openPrompt, TextField} from "prosemirror-example-setup/src/prompt"

const offtopicMarkSpec: MarkSpec = {
	parseDOM: [{tag: "span[data-offtopic]"}],
	toDOM: () => ["span", {"data-offtopic": "", class: "offtopic"}, 0],
}

const neuzenMarkSpec: MarkSpec = {
	parseDom: [{tag: "span[data-neuzen]"}],
	toDOM: () => ["span", {"data-neuzen": ""}, 0],  // Geen implementatie nu
}

const priveSpec: MarkSpec = {
	attrs: {prive: {default: null}},
	parseDOM: [{tag: "span[data-prive]"}],
	toDOM: (node) => ["span", {
		"data-prive": node.attrs.prive,
		class: "bb-prive",
		title: `Prive: ${node.attrs.prive || "P_LOGGED_IN"}`
	}, 0],
}

export const addBbMarks = <T extends { addToEnd: (name: string, spec: MarkSpec) => T }>(marks: T): T => {
	return marks.addToEnd('offtopic', offtopicMarkSpec)
		.addToEnd('neuzen', neuzenMarkSpec)
		.addToEnd('prive', priveSpec)
}

function cmdItem(cmd, options) {
	const passedOptions = {
		label: options.title,
		run: cmd
	}
	for (const prop in options) passedOptions[prop] = options[prop]
	if ((!options.enable || options.enable === true) && !options.select)
		passedOptions[options.enable ? "enable" : "select"] = state => cmd(state)

	return new MenuItem(passedOptions)
}

function markActive(state, type) {
	const {from, $from, to, empty} = state.selection
	if (empty) return type.isInSet(state.storedMarks || $from.marks())
	else return state.doc.rangeHasMark(from, to, type)
}

function markItem(markType, options) {
	const passedOptions = {
		active(state) {
			return markActive(state, markType)
		},
		enable: true
	}
	for (const prop in options) passedOptions[prop] = options[prop]
	return cmdItem(toggleMark(markType), passedOptions)
}

function priveItem(markType) {
	return new MenuItem({
		title: "Markeer tekst als prive",
		icon: icons.link,
		active(state) {
			return markActive(state, markType)
		},
		enable(state) {
			return !state.selection.empty
		},
		run(state, dispatch, view) {
			if (markActive(state, markType)) {
				toggleMark(markType)(state, dispatch)
				return true
			}
			openPrompt({
				title: "Markeer als prive",
				fields: {
					prive: new TextField({label: "Rechten (leeg voor ingelogd)"}),
				},
				callback(attrs) {
					toggleMark(markType, attrs)(view.state, view.dispatch)
					view.focus()
				}
			})
		}
	})
}

export const buildBbMarksMenu = (schema: Schema, menu: Record<string, any>): Record<string, any> => {
	menu.inlineMenu[0].push(markItem(schema.marks.offtopic, {title: "Schakel offtopic", icon: icons.strong}))
	menu.inlineMenu[0].push(markItem(schema.marks.neuzen, {title: "Schakel neuzen", icon: icons.strong}))
	menu.inlineMenu[0].push(priveItem(schema.marks.prive))

	return menu;
}

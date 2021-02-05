import {EditorSchema} from "./schema";
import {bbPrompt} from "./bb-prompt";
import {EditorState, NodeSelection} from "prosemirror-state";
import {MarkType, NodeType} from "prosemirror-model";
import {blockTypeItem, icons, MenuItem} from "prosemirror-menu";
import {FileField, openPrompt, TextField} from "./prompt";
import {toggleMark} from "prosemirror-commands";
import {wrapInList} from "prosemirror-schema-list";
import {startImageUpload} from "./forum-plaatje";
import {ucfirst} from "../lib/util";

export function canInsert(state: EditorState<EditorSchema>, nodeType: NodeType<EditorSchema>): boolean {
	const $from = state.selection.$from
	for (let d = $from.depth; d >= 0; d--) {
		const index = $from.index(d)
		if ($from.node(d).canReplaceWith(index, index, nodeType)) return true
	}
	return false
}

export const insertImageItem = (nodeType: NodeType<EditorSchema>): MenuItem => new MenuItem({
	title: "Afbeelding invoegen",
	label: "Afbeelding",
	enable(state) {
		return canInsert(state, nodeType)
	},
	run(state, _, view) {
		// const {from, to} = state.selection
		let attrs = null
		if (state.selection instanceof NodeSelection && state.selection.node.type == nodeType)
			attrs = state.selection.node.attrs
		openPrompt({
			title: "Afbeelding invoegen",
			fields: {
				src: new TextField({label: "Locatie", required: true, value: attrs && attrs.src}),
				// TODO: Support ook title en alt in bb
				// title: new TextField({label: "Titel", value: attrs && attrs.title}),
				// alt: new TextField({
				// 	label: "Beschrijving",
				// 	value: attrs ? attrs.alt : state.doc.textBetween(from, to, " ")
				// })
			},
			callback: callbackAttrs => {
				view.dispatch(view.state.tr.replaceSelectionWith(nodeType.createAndFill(callbackAttrs)))
				view.focus()
			}
		})
	}
});

function cmdItem(cmd: (state: EditorState) => boolean, options) {
	const passedOptions = {
		label: options.title,
		run: cmd,
		...options
	}

	if ((!options.enable || options.enable === true) && !options.select)
		passedOptions[options.enable ? "enable" : "select"] = state => cmd(state)

	return new MenuItem(passedOptions)
}

function markActive(state: EditorState<EditorSchema>, type: MarkType<EditorSchema>) {
	const {from, $from, to, empty} = state.selection
	if (empty) return !!type.isInSet(state.storedMarks || $from.marks())
	else return state.doc.rangeHasMark(from, to, type)
}

export const markItem = (markType: MarkType<EditorSchema>, options): MenuItem => cmdItem(toggleMark(markType), {
	active(state) {
		return markActive(state, markType)
	},
	enable: true,
	...options
});

export const linkItem = (markType: MarkType<EditorSchema>): MenuItem => new MenuItem({
	title: "Maak of verwijder link",
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
			title: "Maak een link",
			fields: {
				href: new TextField({
					label: "Link doel",
					required: true
				}),
				title: new TextField({label: "Titel"})
			},
			callback(attrs) {
				toggleMark(markType, attrs)(view.state, view.dispatch)
				view.focus()
			}
		})
	}
});

export const wrapListItem = (nodeType: NodeType<EditorSchema>, options): MenuItem => cmdItem(wrapInList(nodeType, options.attrs), options);

export const blockTypeHead = (nodeType: NodeType, i: number): MenuItem => blockTypeItem(nodeType, {
	title: "Verander naar kop " + i,
	label: "Kop " + i,
	attrs: {level: i}
});

export const priveItem = (markType: MarkType): MenuItem => new MenuItem({
	title: "Markeer tekst als prive",
	label: "Prive",
	active: state => markActive(state, markType),
	enable: state => !state.selection.empty,
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
});

export const blockTypeItemPrompt = (nodeType: NodeType<EditorSchema>, label: string, title: string, description = ""): MenuItem => new MenuItem({
	title,
	label,
	enable: state => canInsert(state, nodeType),
	run: (state, dispatch, view) => {
		let attrs = null
		let content = null

		if (state.selection instanceof NodeSelection && state.selection.node.type == nodeType) {
			attrs = state.selection.node.attrs
			content = state.selection.node.content
		}

		openPrompt({
			description,
			title: attrs ? "Update: " + nodeType.name : "Invoegen: " + nodeType.name,
			fields: Object.fromEntries(Object.entries(nodeType.spec.attrs).map(([attr, spec]) =>
				[attr, new TextField({
					label: ucfirst(attr),
					required: spec.default === undefined,
					value: attrs ? attrs[attr] : spec.default
				})])),
			callback(callbackAttrs) {
				view.dispatch(view.state.tr.replaceSelectionWith(nodeType.createAndFill({type: nodeType.name, ...callbackAttrs}, content)))
				view.focus()
			}
		})
	}
});

export const bbInsert = (nodeType: NodeType<EditorSchema>): MenuItem => new MenuItem({
	title: "BB code als platte tekst invoegen",
	label: "BB code",
	enable: state => canInsert(state, nodeType),
	run: (state, dispatch, view) => {
		let attrs: Record<string, string> = {bb: ""}

		if (state.selection instanceof NodeSelection && state.selection.node.type == nodeType) {
			attrs = state.selection.node.attrs
		}

		bbPrompt(nodeType, attrs, view)
	}
});

export const insertPlaatjeItem = (nodeType: NodeType<EditorSchema>): MenuItem => new MenuItem<any>({
	title: "Plaatje uploaden",
	label: "Plaatje",
	enable: state => canInsert(state, nodeType),
	run: (state, dispatch, view) => {

		openPrompt({
			title: "Plaatje uploaden",
			fields: {
				image: new FileField({label: "Afbeelding"})
			},
			callback: params => {
				startImageUpload(view, params.image)
			}
		})
	}
})

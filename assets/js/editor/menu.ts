import {
	blockTypeItem,
	Dropdown,
	DropdownSubmenu,
	icons,
	joinUpItem,
	liftItem,
	MenuItem,
	selectParentNodeItem,
	wrapItem
} from "prosemirror-menu"
import {EditorState, NodeSelection} from "prosemirror-state"
import {toggleMark} from "prosemirror-commands"
import {wrapInList} from "prosemirror-schema-list"
import {openPrompt, TextField} from "prosemirror-example-setup/src/prompt"
import {MarkType, NodeType} from "prosemirror-model";
import {blocks, EditorSchema} from "./schema";
import {redo, undo} from "prosemirror-history";
import {bbPrompt} from "./bb-prompt";

function canInsert(state: EditorState<EditorSchema>, nodeType: NodeType<EditorSchema>) {
	const $from = state.selection.$from
	for (let d = $from.depth; d >= 0; d--) {
		const index = $from.index(d)
		if ($from.node(d).canReplaceWith(index, index, nodeType)) return true
	}
	return false
}

function insertImageItem(nodeType: NodeType<EditorSchema>) {
	return new MenuItem({
		title: "Insert image",
		label: "Image",
		enable(state) {
			return canInsert(state, nodeType)
		},
		run(state, _, view) {
			const {from, to} = state.selection
			let attrs = null
			if (state.selection instanceof NodeSelection && state.selection.node.type == nodeType)
				attrs = state.selection.node.attrs
			openPrompt({
				title: "Insert image",
				fields: {
					src: new TextField({label: "Location", required: true, value: attrs && attrs.src}),
					title: new TextField({label: "Title", value: attrs && attrs.title}),
					alt: new TextField({
						label: "Description",
						value: attrs ? attrs.alt : state.doc.textBetween(from, to, " ")
					})
				},
				callback: callbackAttrs => {
					view.dispatch(view.state.tr.replaceSelectionWith(nodeType.createAndFill(callbackAttrs)))
					view.focus()
				}
			})
		}
	})
}

function cmdItem(cmd, options) {
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

function markItem(markType: MarkType<EditorSchema>, options) {
	const passedOptions = {
		active(state) {
			return markActive(state, markType)
		},
		enable: true,
		...options
	}

	return cmdItem(toggleMark(markType), passedOptions)
}

function linkItem(markType: MarkType<EditorSchema>) {
	return new MenuItem({
		title: "Add or remove link",
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
				title: "Create a link",
				fields: {
					href: new TextField({
						label: "Link target",
						required: true
					}),
					title: new TextField({label: "Title"})
				},
				callback(attrs) {
					toggleMark(markType, attrs)(view.state, view.dispatch)
					view.focus()
				}
			})
		}
	})
}

function wrapListItem(nodeType: NodeType<EditorSchema>, options) {
	return cmdItem(wrapInList(nodeType, options.attrs), options)
}

function blockTypeHead(nodeType, i) {
	return blockTypeItem(nodeType, {
		title: "Change to heading " + i,
		label: "Level " + i,
		attrs: {level: i}
	})
}

function priveItem(markType) {
	return new MenuItem({
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
	})
}

const canInsertBlock = (state: EditorState<EditorSchema>) => {
	const {$from} = state.selection
	const blockType = state.schema.nodes["bb-block"];

	for (let d = $from.depth; d >= 0; d--) {
		const index = $from.index(d)
		if ($from.node(d).canReplaceWith(index, index, blockType)) return true
	}

	return false;
}

function blockMenuItem(type: string, fields: string[]) {
	return new MenuItem({
		title: "Insert " + type,
		label: type.charAt(0).toUpperCase() + type.slice(1),
		enable: canInsertBlock,
		run(state, dispatch, view) {
			let attrs = null
			const blockType = state.schema.nodes["bb-block"]

			if (state.selection instanceof NodeSelection && state.selection.node.type == blockType) {
				attrs = state.selection.node.attrs
			}

			if (fields.length > 0) {
				openPrompt({
					title: attrs && attrs.type == type ? "Update: " + attrs.type : "Invoegen: " + type,
					fields: Object.fromEntries(
						fields.map(field =>
							[field, new TextField({label: field, required: true, value: attrs && attrs[field]})])),
					callback(callbackAttrs) {
						view.dispatch(view.state.tr.replaceSelectionWith(blockType.createAndFill({type, ...callbackAttrs})))
						view.focus()
					}
				})
			} else {
				dispatch(state.tr.replaceSelectionWith(blockType.createAndFill({type, ...attrs})))
			}
		}
	})
}

function blockTypeItemPrompt(nodeType: NodeType<EditorSchema>, options) {
	return new MenuItem({
		title: options.title,
		label: options.label,
		enable: state => canInsert(state, nodeType),
		run: (state, dispatch, view) => {
			let attrs = null

			if (state.selection instanceof NodeSelection && state.selection.node.type == nodeType) {
				attrs = state.selection.node.attrs
			}

			openPrompt({
				title: attrs ? "Update: " + nodeType.name : "Invoegen: " + nodeType.name,
				fields: Object.fromEntries(Object.entries(nodeType.spec.attrs).map(([attr, spec]) =>
					[attr, new TextField({label: attr, required: true, value: attrs ? attrs[attr] : spec.default})])),
				callback(callbackAttrs) {
					view.dispatch(view.state.tr.replaceSelectionWith(nodeType.createAndFill({type: nodeType.name, ...callbackAttrs})))
					view.focus()
				}
			})
		}
	})
}

function bbInsert(nodeType: NodeType<EditorSchema>) {
	return new MenuItem({
		title: "BB code als platte tekst invoegen",
		label: "BB code",
		enable: state => canInsert(state, nodeType),
		run: (state, dispatch, view) => {
			let attrs: any = {bb: ""}

			if (state.selection instanceof NodeSelection && state.selection.node.type == nodeType) {
				attrs = state.selection.node.attrs
			}

			bbPrompt(nodeType, attrs, view)
		}
	})
}

export function buildMenuItems(schema: EditorSchema): (MenuItem | Dropdown)[][] {
	return [
		[
			markItem(schema.marks.strong, {title: "Schakel dikgedrukt", icon: icons.strong}),
			markItem(schema.marks.em, {title: "Schakel schuingedrukt", icon: icons.em}),
			linkItem(schema.marks.link),
			new Dropdown([
				markItem(schema.marks.code, {title: "Schakel code", label: "Code"}),
				markItem(schema.marks.neuzen, {title: "Schakel neuzen", label: "Neuzen"}),
				markItem(schema.marks.superscript, {title: "Schakel superscript", label: "Superscript"}),
				markItem(schema.marks.subscript, {title: "Schakel subscript", label: "Subscript"}),
				markItem(schema.marks.underline, {title: "Schakel onderlijn", label: "Onderlijn"}),
				markItem(schema.marks.strikethrough, {title: "Schakel doorstreep", label: "Doorstreep"}),
				markItem(schema.marks.offtopic, {title: "Schakel offtopic", label: "Offtopic"}),
				priveItem(schema.marks.prive),
			], {label: "Meer"})
		],
		[
			new Dropdown([
				insertImageItem(schema.nodes.image),
				new DropdownSubmenu([
					blockTypeItemPrompt(schema.nodes.twitter, {title: "Twitter invoegen", label: "Twitter"}),
					blockTypeItemPrompt(schema.nodes.youtube, {title: "YouTube invoegen", label: "YouTube"}),
					blockTypeItemPrompt(schema.nodes.spotify, {title: "Spotify invoegen", label: "Spotify"}),
					blockTypeItemPrompt(schema.nodes.video, {title: "Video invoegen", label: "Video"}),
					blockTypeItemPrompt(schema.nodes.audio, {title: "Geluid invoegen", label: "Geluid"}),
				], {label: "Embed"}),
				...Object.entries(blocks).map(([name, fields]) => blockMenuItem(name, fields)),
				new MenuItem({
					title: "Insert horizontal rule",
					label: "Horizontal rule",
					enable(state) {
						return canInsert(state, schema.nodes.horizontal_rule)
					},
					run(state, dispatch) {
						dispatch(state.tr.replaceSelectionWith(schema.nodes.horizontal_rule.create()))
					}
				}),
				wrapItem(schema.nodes.verklapper, {
					title: "Stop selectie in verklapper",
					label: "Verklapper"
				}),
				bbInsert(schema.nodes.bb),
			], {label: "Invoegen"}),
			new Dropdown([
				blockTypeItem(schema.nodes.paragraph, {
					title: "Change to paragraph",
					label: "Plain"
				}),
				blockTypeItem(schema.nodes.code_block, {
					title: "Change to code block",
					label: "Code"
				}),
				new DropdownSubmenu([
					blockTypeHead(schema.nodes.heading, 1),
					blockTypeHead(schema.nodes.heading, 2),
					blockTypeHead(schema.nodes.heading, 3),
					blockTypeHead(schema.nodes.heading, 4),
					blockTypeHead(schema.nodes.heading, 5),
					blockTypeHead(schema.nodes.heading, 6),
				], {label: "Heading"})
			], {label: "Type..."}),
		],
		[
			new MenuItem({
				title: "Undo last change",
				run: undo,
				enable: state => undo(state),
				icon: icons.undo
			}),
			new MenuItem({
				title: "Redo last undone change",
				run: redo,
				enable: state => redo(state),
				icon: icons.redo
			})
		],
		[
			wrapListItem(schema.nodes.bullet_list, {
				title: "Wrap in bullet list",
				icon: icons.bulletList
			}),
			wrapListItem(schema.nodes.ordered_list, {
				title: "Wrap in ordered list",
				icon: icons.orderedList
			}),
			wrapItem(schema.nodes.blockquote, {
				title: "Wrap in block quote",
				icon: icons.blockquote
			}),
			joinUpItem,
			liftItem,
			selectParentNodeItem
		],
	]
}

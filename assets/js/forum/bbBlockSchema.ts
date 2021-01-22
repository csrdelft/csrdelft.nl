import {NodeSpec} from "prosemirror-model";
import {EditorState, NodeSelection} from "prosemirror-state";
import {MenuItem} from "prosemirror-menu";
import {openPrompt, TextField} from "prosemirror-example-setup/src/prompt"

export const blocks = {
	"groep": ["id"],
	"activiteit": ["id"]
}

export const bbBlockSpec: NodeSpec = {
	attrs: {type: {default: "groep"}, id: {default: "none"}},
	inline: false,
	group: "block",
	draggable: true,
	toDOM: node => ["div", {
		"data-bb-block-type": node.attrs.type,
		class: `pm-block pm-block-${node.attrs.type}`,
		title: node.attrs.type,
	}, `${node.attrs.type}: ${node.attrs.id}`],
	parseDOM: [{
		tag: "div[data-block-type]",
		getAttrs: (dom: HTMLElement) => {
			const type = dom.dataset.bbBlockType
			return type in blocks ? {type} : false
		}
	}]
}

export const buildBbBlockMenu = (menu: Record<string, any>): Record<string, any> => {
	Object.entries(blocks).forEach(([type, fields]) => menu.insertMenu.content.push(new MenuItem({
		title: "Insert " + type,
		label: type.charAt(0).toUpperCase() + type.slice(1),
		enable: canInsertBlock,
		run(state, _, view) {
			let attrs = null
			const blockType = state.schema.nodes["bb-block"]

			if (state.selection instanceof NodeSelection && state.selection.node.type == blockType) {
				attrs = state.selection.node.attrs
			}

			openPrompt({
				title: attrs && attrs.id ? "Update: " + attrs.type : "Invoegen: " + type,
				fields: Object.fromEntries(
					fields.map(field =>
						[field, new TextField({label: field, required: true, value: attrs && attrs[field]})])),
				callback(attrs) {
					view.dispatch(view.state.tr.replaceSelectionWith(blockType.createAndFill({type, ...attrs})))
					view.focus()
				}
			})
		}
	})))
	return menu
}

const canInsertBlock = (state: EditorState) => {
	const {$from} = state.selection
	const blockType = state.schema.nodes["bb-block"];

	for (let d = $from.depth; d >= 0; d--) {
		const index = $from.index(d)
		if ($from.node(d).canReplaceWith(index, index, blockType)) return true
	}

	return false;
}

export const addBbBlock = <T extends { addBefore: (before: string, name: string, spec: NodeSpec) => T }>(nodes: T): T =>
	nodes.addBefore("image", "bb-block", bbBlockSpec)

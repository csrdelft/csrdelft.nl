import {MenuItem} from "prosemirror-menu"
import {EditorState, NodeSelection} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Node, Schema} from "prosemirror-model"
import {schema} from "prosemirror-schema-basic"
import {addListNodes} from "prosemirror-schema-list"
import {buildMenuItems, exampleSetup} from "prosemirror-example-setup"
import {openPrompt, TextField} from "prosemirror-example-setup/src/prompt"
import ctx from "../ctx";
import {bbBlockSpec, blocks} from "./schema";

// Mix the nodes from prosemirror-schema-list into the basic schema to
// create a schema with list support.
const mySchema = new Schema({
	nodes: addListNodes(schema.spec.nodes as any, "paragraph block*", "block")
		.addBefore("image", "bb-block", bbBlockSpec),
	marks: schema.spec.marks
})

const blockType = mySchema.nodes["bb-block"]

const canInsertBlock = state => {
	const {$from} = state.selection

	for (let d = $from.depth; d >= 0; d--) {
		const index = $from.index(d)
		if ($from.node(d).canReplaceWith(index, index, blockType)) return true
	}

	return false;
}

const menu = buildMenuItems(mySchema)

Object.entries(blocks).forEach(([type, fields]) => menu.insertMenu.content.push(new MenuItem({
	title: "Insert " + type,
	label: type.charAt(0).toUpperCase() + type.slice(1),
	enable: canInsertBlock,
	run(state, _, view) {
		let attrs = null

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

ctx.addHandler('.pm-editor', el => {
	const input = document.querySelector<HTMLInputElement>('#' + el.dataset.prosemirrorDoc);

	const doc = JSON.parse(input.value);

	const contentNode = Node.fromJSON(mySchema, doc)

	const view = new EditorView(el, {
		state: EditorState.create({
			doc: contentNode,
			plugins: exampleSetup({schema: mySchema, menuContent: menu.fullMenu})
		}),
		dispatchTransaction(tr) {
			// dispatchTransaction is verantwoordelijk voor het updaten van de state.
			view.updateState(view.state.apply(tr));
			// Synchroniseer state met input veld.
			input.value = JSON.stringify(view.state.doc.toJSON());
		}
	})
})




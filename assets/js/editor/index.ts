import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Node, Schema} from "prosemirror-model"
import {EditorMarks, EditorNodes, schema} from "./schema"
import {addListNodes} from "prosemirror-schema-list"
import {exampleSetup} from "prosemirror-example-setup"
import {buildMenuItems} from "./menu";
import {htmlDecode} from "../lib/util";
import {bbPrompt} from "./bb-prompt";
import ctx from "../ctx";

ctx.addHandler('.pm-editor', (el: HTMLElement): void => {
	// Mix the nodes from prosemirror-schema-list into the basic schema to
	// create a schema with list support.
	const mySchema = new Schema<EditorNodes, EditorMarks>({
		nodes: addListNodes(schema.spec.nodes as any, "paragraph block*", "block"),
		marks: schema.spec.marks,
	})

	const menuContent = buildMenuItems(mySchema)
	const input = document.querySelector<HTMLInputElement>('#' + el.dataset.prosemirrorDoc);
	const text = htmlDecode(input.value.replace(/&quot;/g, "\\\""));

	const contentNode = Node.fromJSON(mySchema, JSON.parse(text))

	const currentView = new EditorView<typeof mySchema>(el, {
		state: EditorState.create({
			doc: contentNode,
			plugins: exampleSetup({schema: mySchema, menuContent})
		}),
		dispatchTransaction(tr) {
			// dispatchTransaction is verantwoordelijk voor het updaten van de state.
			currentView.updateState(currentView.state.apply(tr));
			// Synchroniseer state met input veld.
			input.value = JSON.stringify(currentView.state.doc.toJSON());
		},
		handleDoubleClickOn(view, pos, node) {
			if (node.type == mySchema.nodes.bb) {
				bbPrompt(node.type, node.attrs, view)
			}

			return true
		}
	})
})

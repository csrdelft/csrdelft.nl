import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Node, Schema} from "prosemirror-model"
import {EditorMarks, EditorNodes, schema} from "./schema"
import {addListNodes} from "prosemirror-schema-list"
import {exampleSetup} from "prosemirror-example-setup"
import ctx from "../ctx";
import {buildMenuItems} from "./menu";

// Mix the nodes from prosemirror-schema-list into the basic schema to
// create a schema with list support.
const mySchema = new Schema<EditorNodes, EditorMarks>({
	nodes: addListNodes(schema.spec.nodes as any, "paragraph block*", "block"),
	marks: schema.spec.marks,
})

const menu = buildMenuItems(mySchema)

ctx.addHandler('.pm-editor', el => {
	const input = document.querySelector<HTMLInputElement>('#' + el.dataset.prosemirrorDoc);

	const doc = JSON.parse(input.value);

	const contentNode = Node.fromJSON(mySchema, doc)

	const view = new EditorView(el, {
		state: EditorState.create({
			doc: contentNode,
			plugins: exampleSetup({schema: mySchema, menuContent: menu})
		}),
		dispatchTransaction(tr) {
			// dispatchTransaction is verantwoordelijk voor het updaten van de state.
			view.updateState(view.state.apply(tr));
			// Synchroniseer state met input veld.
			input.value = JSON.stringify(view.state.doc.toJSON());
		}
	})
})

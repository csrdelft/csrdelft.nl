import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Node, Schema} from "prosemirror-model"
import {schema} from "prosemirror-schema-basic"
import {addListNodes} from "prosemirror-schema-list"
import {exampleSetup} from "prosemirror-example-setup"
import ctx from "../ctx";

declare global {
	interface Window {
		view: EditorView
	}
}

// Mix the nodes from prosemirror-schema-list into the basic schema to
// create a schema with list support.
const mySchema = new Schema({
	nodes: addListNodes(schema.spec.nodes as any, "paragraph block*", "block"),
	marks: schema.spec.marks
})

ctx.addHandler('.pm-editor', el => {
	const input = document.querySelector<HTMLInputElement>('#' + el.dataset.prosemirrorDoc);

	const doc = JSON.parse(input.value);

	const content = {
		"doc": doc,
		"selection": {
			"type": "text",
			"anchor": 16,
			"head": 16,
		}
	}

	console.log(doc)

	const contentNode = Node.fromJSON(mySchema, content.doc)

	const view = new EditorView(document.querySelector("#editor"), {
		state: EditorState.create({
			doc: contentNode,
			plugins: exampleSetup({schema: mySchema})
		}),
		dispatchTransaction(tr) {
			view.updateState(view.state.apply(tr));
			//current state as json in text area
			input.value = JSON.stringify(view.state.doc.toJSON());
		}
	})
})




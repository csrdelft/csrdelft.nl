import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Node, Schema} from "prosemirror-model"
import {schema} from "prosemirror-schema-basic"
import {addListNodes} from "prosemirror-schema-list"
import {buildMenuItems, exampleSetup} from "prosemirror-example-setup"
import ctx from "../ctx";
import {addBbBlock, buildBbBlockMenu} from "./bbBlockSchema";
import {addBbVerklapper, buildBbVerklapperMenu} from "./bbVerklapperSchema";
import {addBbMarks, buildBbMarksMenu} from "./bbMarks";

// Mix the nodes from prosemirror-schema-list into the basic schema to
// create a schema with list support.
const mySchema = new Schema({
	nodes: addBbVerklapper(addBbBlock(addListNodes(schema.spec.nodes as any, "paragraph block*", "block"))),
	marks: addBbMarks(schema.spec.marks as any),
})

const menu = buildMenuItems(mySchema)
buildBbBlockMenu(menu)
buildBbVerklapperMenu(mySchema, menu)
buildBbMarksMenu(mySchema, menu)

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

import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Node, Schema} from "prosemirror-model"
import {schema} from "prosemirror-schema-basic"
import {addListNodes} from "prosemirror-schema-list"
import {exampleSetup} from "prosemirror-example-setup"

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
const content = {
	"doc": {
		"type": "doc",
		"content": [{
			"type": "heading",
			"attrs": {"level": 2},
			"content": [{"type": "text", "text": "Dingen met spullen"}]
		}, {
			"type": "paragraph",
			"content": [{"type": "text", "text": "Dit is mijn "}, {
				"type": "text",
				"marks": [{"type": "em"}],
				"text": "verhaal"
			}, {"type": "text", "text": ", wat vindt je er van?"}]
		}, {
			"type": "paragraph",
			"content": [{
				"type": "image",
				"attrs": {"src": "http://dev-csrdelft.nl/profiel/pasfoto/1345.jpg", "alt": "", "title": ""}
			}]
		}, {"type": "horizontal_rule"}, {
			"type": "paragraph",
			"content": [{"type": "text", "marks": [{"type": "em"}], "text": "a"}, {
				"type": "text",
				"marks": [{"type": "em"}, {"type": "strong"}],
				"text": "sdfa"
			}, {"type": "text", "marks": [{"type": "em"}], "text": "sdf"}]
		}]
	},
	"selection": {
		"type": "text",
		"anchor": 16,
		"head": 16,
	}

}

const contentNode = Node.fromJSON(mySchema, content.doc)

window.view = new EditorView(document.querySelector("#editor"), {
	state: EditorState.create({
		doc: contentNode,
		plugins: exampleSetup({schema: mySchema})
	})
})


document.querySelector("#export").addEventListener('click', () => {
	console.log(window.view.state.doc, JSON.stringify(window.view.state.doc))
	document.querySelector<HTMLInputElement>("input#bericht").value = JSON.stringify(window.view.state.doc)
})


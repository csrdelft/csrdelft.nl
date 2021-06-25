import {EditorState} from "prosemirror-state"
import {EditorView} from "prosemirror-view"
import {Node} from "prosemirror-model"
import {EditorSchema, schema} from "./schema"
import {exampleSetup} from "prosemirror-example-setup"
import {buildMenuItems} from "./menu";
import {htmlDecode} from "../lib/util";
import {bbPrompt} from "./bb-prompt";
import ctx from "../ctx";
import {placeholderPlugin} from "./plugin/placeholder";
import {trackChangesPlugin} from "./plugin/track-changes";
import {lidHintPlugin} from "./plugin/lid-hint";
import {imageRemovePlugin, imageUploadPlugin} from "./plugin/image-upload";

declare global {
	interface Window {
		// Huidige editor, referentie voor citeren enzo
		currentEditor: EditorView<EditorSchema>
	}
}

ctx.addHandler('.pm-editor', (el: HTMLElement): void => {
	const extern = el.dataset.extern;

	const menuContent = buildMenuItems(schema, window.loggedIn && !extern)
	const input = document.querySelector<HTMLInputElement>('#' + el.dataset.prosemirrorDoc);
	const text = htmlDecode(input.value.replace(/&quot;/g, "\\\""));

	window.currentEditor = new EditorView<EditorSchema>(el, {
		state: EditorState.create({
			doc: Node.fromJSON(schema, JSON.parse(text)),
			plugins: [
				lidHintPlugin,
				(window.loggedIn ? imageUploadPlugin(schema) : imageRemovePlugin(schema)),
				...exampleSetup({
					schema,
					menuContent
				}),
				placeholderPlugin,
				trackChangesPlugin(input),
			]
		}),
		handleDoubleClickOn(view, pos, node) {
			if (node.type == schema.nodes.bb) {
				bbPrompt(node.type, node.attrs, view)
			}

			return true
		}
	})
})

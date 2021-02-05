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

declare global {
	interface Window {
		editor: EditorView<EditorSchema>
	}
}

ctx.addHandler('.pm-editor', (el: HTMLElement): void => {
	const menuContent = buildMenuItems(schema)
	const input = document.querySelector<HTMLInputElement>('#' + el.dataset.prosemirrorDoc);
	const text = htmlDecode(input.value.replace(/&quot;/g, "\\\""));

	window.editor = new EditorView<EditorSchema>(el, {
		state: EditorState.create({
			doc: Node.fromJSON(schema, JSON.parse(text)),
			plugins: exampleSetup({schema, menuContent}).concat(placeholderPlugin, trackChangesPlugin(input), lidHintPlugin)
		}),
		handleDoubleClickOn(view, pos, node) {
			if (node.type == schema.nodes.bb) {
				bbPrompt(node.type, node.attrs, view)
			}

			return true
		}
	})
})

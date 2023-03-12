import { EditorState, Plugin } from 'prosemirror-state';
import { EditorView } from 'prosemirror-view';
import { Node } from 'prosemirror-model';
import { schema } from './schema';
import { history } from 'prosemirror-history';
import { buildInputRules, buildKeymap } from 'prosemirror-example-setup';
import { buildMenuItems } from './menu';
import { htmlDecode } from '../lib/util';
import { bbPrompt } from './bb-prompt';
import ctx from '../ctx';
import { placeholderPlugin } from './plugin/placeholder';
import { trackChangesPlugin } from './plugin/track-changes';
import { lidHintPlugin } from './plugin/lid-hint';
import { imageRemovePlugin, imageUploadPlugin } from './plugin/image-upload';

import './citeer';
import { baseKeymap } from 'prosemirror-commands';
import { dropCursor } from 'prosemirror-dropcursor';
import { gapCursor } from 'prosemirror-gapcursor';
import { keymap } from 'prosemirror-keymap';
import { menuBar } from 'prosemirror-menu';

declare global {
	interface Window {
		// Huidige editor, referentie voor citeren enzo
		currentEditor: EditorView;
	}
}

ctx.addHandler('.pm-editor', (el: HTMLElement): void => {
	const extern = el.dataset.extern;

	const menuContent = buildMenuItems(schema, window.loggedIn && !extern);
	const input = document.querySelector<HTMLInputElement>(
		'#' + el.dataset.prosemirrorDoc
	);
	const text = htmlDecode(input.value.replace(/&quot;/g, '\\"'));

	window.currentEditor = new EditorView(el, {
		state: EditorState.create({
			doc: Node.fromJSON(schema, JSON.parse(text)),
			plugins: [
				...lidHintPlugin,
				window.loggedIn && !extern
					? imageUploadPlugin(schema)
					: imageRemovePlugin(schema),
				buildInputRules(schema),
				keymap(buildKeymap(schema, null)),
				keymap(baseKeymap),
				dropCursor(),
				gapCursor(),
				history(),
				menuBar({
					floating: true,
					content: menuContent,
				}),
				new Plugin({
					props: {
						attributes: { class: 'ProseMirror-example-setup-style' },
					},
				}),
				placeholderPlugin,
				trackChangesPlugin(input),
			],
		}),
		handleDoubleClickOn(view, pos, node) {
			if (node.type == schema.nodes.bb) {
				bbPrompt(node.type, node.attrs, view);
			}

			return true;
		},
	});
});

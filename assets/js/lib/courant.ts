import { Node } from 'prosemirror-model';
import axios from 'axios';

export async function importAgenda(): Promise<void> {
	const response = await axios.post('/agenda/courant');

	const editor = window.currentEditor;
	const node = Node.fromJSON(editor.state.schema, response.data[0]);

	editor.dispatch(editor.state.tr.replaceSelectionWith(node));
}

export function importSponsor(bb: string): void {
	const editor = window.currentEditor;

	const node = editor.state.schema.nodes.bb.create({ bb });

	editor.dispatch(editor.state.tr.replaceSelectionWith(node));
}

import $ from 'jquery';
import { Node } from 'prosemirror-model';

export function importAgenda(): void {
	$.ajax({
		cache: false,
		data: '',
		type: 'POST',
		url: '/agenda/courant',
	}).done((data) => {
		const editor = window.currentEditor;
		const node = Node.fromJSON(editor.state.schema, data[0]);

		editor.dispatch(editor.state.tr.replaceSelectionWith(node));
	});
}

export function importSponsor(bb: string): void {
	const editor = window.currentEditor;

	const node = editor.state.schema.nodes.bb.create({ bb });

	editor.dispatch(editor.state.tr.replaceSelectionWith(node));
}

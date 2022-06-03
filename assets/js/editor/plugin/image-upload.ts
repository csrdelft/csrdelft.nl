import { Plugin, Transaction } from 'prosemirror-state';
import { EditorSchema } from '../schema';
import { placeholderPlugin } from './placeholder';
import { imageUpload } from '../forum-plaatje';
import { base64toFile } from '../../lib/util';

/**
 * Verwijderd plaatjes zonder url. (Extern)
 * @param schema
 */
export const imageRemovePlugin = (schema: EditorSchema): Plugin<unknown, EditorSchema> =>
	new Plugin<unknown, EditorSchema>({
		appendTransaction(trs, oldState, newState) {
			let newTransaction: Transaction<EditorSchema> = null;
			newState.doc.descendants((node, pos) => {
				if (node.type == schema.nodes.image && node.attrs.src.startsWith('data:')) {
					newTransaction = (newTransaction ?? newState.tr).deleteRange(pos, pos + 1);
				}
			});

			return newTransaction;
		},
	});

/**
 * Upload plaatjes zonder url. (Intern)
 * @param schema
 */
export const imageUploadPlugin = (schema: EditorSchema): Plugin<unknown, EditorSchema> => {
	let view = null;
	return new Plugin<unknown, EditorSchema>({
		view: (newView) => {
			view = newView;

			return {};
		},
		appendTransaction(trs, oldState, newState) {
			let newTransaction: Transaction<EditorSchema> = null;
			newState.doc.descendants((node, pos) => {
				if (node.type == schema.nodes.image && node.attrs.src.startsWith('data:')) {
					// Found an image
					const id = {};
					// Verwijder de afbeelding.
					newTransaction = newState.tr.deleteRange(pos, pos + 1);
					// Zorg ervoor dat er een placeholder op deze plek wordt neergezet.
					newTransaction.setMeta(placeholderPlugin, { add: { id, pos } });
					(async () => {
						// Converteer de data attribuut naar een file
						const file = await base64toFile(node.attrs.src, 'pastedImage');

						await imageUpload(view, file, id);
					})();
				}
			});

			return newTransaction;
		},
	});
};

import { findPlaceholder, placeholderPlugin } from './plugin/placeholder';
import { EditorView } from 'prosemirror-view';
import axios from 'axios';
import { preloadImage } from '../lib/util';

/**
 * Wordt opgepikt door de ImageField in PlaatjesUploadModalForm.
 * @param file
 */
async function uploadFile(file: File) {
	const data = new FormData();
	data.append('image_HF', file, file.name);
	data.append('image', 'image_HF');

	const response = await axios.post<{ src: string; key: string }>(
		'/forum/plaatjes/upload_json',
		data,
		{
			headers: { 'content-type': 'multipart/form-data' },
		}
	);

	return response.data;
}

export async function startImageUpload(
	view: EditorView,
	file: File
): Promise<void> {
	// A fresh object to act as the ID for this upload
	const id = {};

	// Replace the selection with a placeholder
	const tr = view.state.tr;
	if (!tr.selection.empty) tr.deleteSelection();
	tr.setMeta(placeholderPlugin, { add: { id, pos: tr.selection.from } });
	view.dispatch(tr);

	await imageUpload(view, file, id);
}

export async function imageUpload(
	view: EditorView,
	file: File,
	id: unknown
): Promise<void> {
	try {
		const { src, key } = await uploadFile(file);

		// Voorkom dat de view eventjes leeg is
		await preloadImage(src);

		const pos = findPlaceholder(view.state, id);
		// If the content around the placeholder has been deleted, drop the image
		if (pos == null) return;
		// Otherwise, insert it at the placeholder's position, and remove the placeholder

		view.dispatch(
			view.state.tr
				.replaceWith(
					pos,
					pos,
					view.state.schema.nodes.plaatje.create({ src, key })
				)
				.setMeta(placeholderPlugin, { remove: { id } })
		);
	} catch (e) {
		// On failure, just clean up the placeholder
		view.dispatch(view.state.tr.setMeta(placeholderPlugin, { remove: { id } }));

		// Rethrow
		throw e;
	}
}

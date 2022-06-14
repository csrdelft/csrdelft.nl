import { EditorSchema } from './schema';
import { bbPrompt } from './bb-prompt';
import { EditorState, NodeSelection } from 'prosemirror-state';
import { MarkType, NodeType } from 'prosemirror-model';
import { MenuItem, MenuItemSpec } from 'prosemirror-menu';
import {
	FileField,
	Label,
	LidField,
	openPrompt,
	TextField,
	YoutubeField,
} from './prompt';
import { toggleMark } from 'prosemirror-commands';
import { wrapInList } from 'prosemirror-schema-list';
import { startImageUpload } from './forum-plaatje';
import { html, ucfirst, uidLike } from '../lib/util';
import Vue from 'vue';

export function canInsert(
	state: EditorState<EditorSchema>,
	nodeType: NodeType<EditorSchema>
): boolean {
	const $from = state.selection.$from;
	for (let d = $from.depth; d >= 0; d--) {
		const index = $from.index(d);
		if ($from.node(d).canReplaceWith(index, index, nodeType)) return true;
	}
	return false;
}

/**
 * Alleen voor externen
 * @param nodeType
 */
export const insertImageItem = (nodeType: NodeType<EditorSchema>): MenuItem =>
	new MenuItem({
		title: 'Afbeelding invoegen',
		label: 'Afbeelding',
		enable(state) {
			return canInsert(state, nodeType);
		},
		run(state, _, view) {
			let attrs = null;
			if (
				state.selection instanceof NodeSelection &&
				state.selection.node.type == nodeType
			)
				attrs = state.selection.node.attrs;
			openPrompt({
				title: 'Afbeelding invoegen',
				fields: {
					src: new TextField({
						label: 'Locatie',
						required: true,
						value: attrs && attrs.src,
					}),
				},
				callback: (callbackAttrs) => {
					view.dispatch(
						view.state.tr.replaceSelectionWith(
							nodeType.createAndFill(callbackAttrs)
						)
					);
					view.focus();
				},
			});
		},
	});

export function insertCitaat(nodeType: NodeType): MenuItem {
	return new MenuItem({
		label: 'Citaat',
		title: 'Citaat invoegen',
		enable: (state) => canInsert(state, nodeType),
		run: (state, dispatch, view) => {
			let attrs = null;
			let content = null;

			if (
				state.selection instanceof NodeSelection &&
				state.selection.node.type == nodeType
			) {
				attrs = state.selection.node.attrs;
				content = state.selection.node.content;
			}

			openPrompt({
				description: '',
				title: attrs
					? 'Update: ' + nodeType.name
					: 'Invoegen: ' + nodeType.name,
				fields: {
					lid: new LidField({
						label: 'Lid',
						required: false,
						value:
							attrs && uidLike(attrs.van)
								? { naam: attrs.naam, uid: attrs.van }
								: { naam: '', uid: '' },
					}),
					of: new Label({ label: '', value: '- Of -' }),
					naam: new TextField({
						label: 'Van',
						required: false,
						value: attrs && !uidLike(attrs.van) ? attrs.naam : '',
					}),
					url: new TextField({
						label: 'Url',
						required: false,
						value: attrs && !uidLike(attrs.van) ? attrs.url : '',
					}),
				},
				callback({ lid, naam, url }) {
					const newAttrs = lid.uid
						? { naam: lid.naam, van: lid.uid }
						: { van: naam, naam, url };

					view.dispatch(
						view.state.tr.replaceSelectionWith(
							nodeType.createAndFill(
								{ type: nodeType.name, ...newAttrs },
								content
							)
						)
					);
					view.focus();
				},
			});
		},
	});
}

function cmdItem(
	cmd: (state: EditorState) => boolean,
	{ enabled, ...options }: Partial<MenuItemSpec> & { enabled?: boolean }
) {
	const passedOptions: MenuItemSpec = {
		label: typeof options.title == 'string' ? options.title : '',
		run: cmd,
		...options,
	};

	if ((!enabled || enabled === true) && !options.select)
		passedOptions[enabled ? 'enable' : 'select'] = (state) => cmd(state);

	return new MenuItem(passedOptions);
}

function markActive(
	state: EditorState<EditorSchema>,
	type: MarkType<EditorSchema>
) {
	const { from, $from, to, empty } = state.selection;
	if (empty) return !!type.isInSet(state.storedMarks || $from.marks());
	else return state.doc.rangeHasMark(from, to, type);
}

export const markItem = (
	markType: MarkType<EditorSchema>,
	options: Partial<MenuItemSpec>
): MenuItem =>
	cmdItem(toggleMark(markType), {
		active(state) {
			return markActive(state, markType);
		},
		enabled: true,
		...options,
	});

export const linkItem = (markType: MarkType<EditorSchema>): MenuItem =>
	new MenuItem({
		title: 'Maak of verwijder link',
		// TODO: maak een helper functie voor icoontjes
		icon: { dom: html`<i class="fa fa-link" aria-hidden="true"></i>` },
		active(state) {
			return markActive(state, markType);
		},
		enable() {
			return true;
		},
		run(state, dispatch, view) {
			if (markActive(state, markType)) {
				toggleMark(markType)(state, dispatch);
				return true;
			}

			if (state.selection.empty) {
				openPrompt({
					title: 'Maak een link',
					fields: {
						tekst: new TextField({
							label: 'Link tekst',
							required: true,
						}),
						href: new TextField({
							label: 'Link doel',
							required: true,
						}),
					},
					callback({ tekst, href }) {
						// Voeg https toe als dat er nog niet staat.
						if (
							!(
								href.startsWith('/') ||
								href.startsWith('https://') ||
								href.startsWith('http://')
							)
						) {
							href = 'https://' + href;
						}

						view.dispatch(
							view.state.tr.replaceSelectionWith(
								state.schema.text(tekst).mark([markType.create({ href })]),
								false
							)
						);
						view.focus();
					},
				});
			} else {
				openPrompt({
					title: 'Maak een link',
					fields: {
						href: new TextField({
							label: 'Link doel',
							required: true,
						}),
					},
					callback({ href }) {
						// Voeg https toe als dat er nog niet staat.
						if (
							!(
								href.startsWith('/') ||
								href.startsWith('https://') ||
								href.startsWith('http://')
							)
						) {
							href = 'https://' + href;
						}
						toggleMark(markType, { href })(view.state, view.dispatch);
						view.focus();
					},
				});
			}
		},
	});

export const wrapListItem = (
	nodeType: NodeType<EditorSchema>,
	options: Partial<MenuItemSpec>
): MenuItem => cmdItem(wrapInList(nodeType, null), options);

export const priveItem = (markType: MarkType): MenuItem =>
	new MenuItem({
		title: 'Markeer tekst als prive',
		label: 'Prive',
		active: (state) => markActive(state, markType),
		enable: (state) => !state.selection.empty,
		run(state, dispatch, view) {
			if (markActive(state, markType)) {
				toggleMark(markType)(state, dispatch);
				return true;
			}
			openPrompt({
				title: 'Markeer als prive',
				fields: {
					prive: new TextField({ label: 'Rechten (leeg voor ingelogd)' }),
				},
				callback(attrs) {
					toggleMark(markType, attrs)(view.state, view.dispatch);
					view.focus();
				},
			});
		},
	});

export const lidInsert = (nodeType: NodeType<EditorSchema>): MenuItem =>
	new MenuItem({
		title: 'Lid invoegen',
		label: 'Lid',
		enable: (state) => canInsert(state, nodeType),
		run: (state, dispatch, view) => {
			let attrs = null;
			let content = null;

			if (
				state.selection instanceof NodeSelection &&
				state.selection.node.type == nodeType
			) {
				attrs = state.selection.node.attrs;
				content = state.selection.node.content;
			}

			openPrompt({
				title: attrs
					? 'Update: ' + nodeType.name
					: 'Invoegen: ' + nodeType.name,
				description:
					"Tip: Type '@' met een zoekterm in je bericht om snel een lid te noemen.",
				fields: {
					lid: new LidField({
						label: 'Lid',
						required: true,
						value: attrs
							? { uid: attrs.uid, naam: attrs.naam }
							: { uid: '', naam: '' },
					}),
				},
				callback(callbackAttrs) {
					view.dispatch(
						view.state.tr.replaceSelectionWith(
							nodeType.createAndFill(
								{ type: nodeType.name, ...callbackAttrs.lid },
								content
							)
						)
					);
					view.focus();
				},
			});
		},
	});

export const blockTypeItemPrompt = (
	nodeType: NodeType<EditorSchema>,
	label: string,
	title: string,
	description = ''
): MenuItem =>
	new MenuItem({
		title,
		label,
		enable: (state) => canInsert(state, nodeType),
		run: (state, dispatch, view) => {
			let attrs = null;
			let content = null;

			if (
				state.selection instanceof NodeSelection &&
				state.selection.node.type == nodeType
			) {
				attrs = state.selection.node.attrs;
				content = state.selection.node.content;
			}

			openPrompt({
				description,
				title: attrs
					? 'Update: ' + nodeType.name
					: 'Invoegen: ' + nodeType.name,
				fields: Object.fromEntries(
					Object.entries(nodeType.spec.attrs).map(([attr, spec]) => [
						attr,
						new TextField({
							label: ucfirst(attr),
							required: spec.default === undefined,
							value: attrs ? attrs[attr] : spec.default,
						}),
					])
				),
				callback(callbackAttrs) {
					view.dispatch(
						view.state.tr.replaceSelectionWith(
							nodeType.createAndFill(
								{ type: nodeType.name, ...callbackAttrs },
								content
							)
						)
					);
					view.focus();
				},
			});
		},
	});

export const groepPrompt = (
	nodeType: NodeType<EditorSchema>,
	label: string,
	title: string,
	type: string
): MenuItem =>
	new MenuItem({
		title,
		label,
		enable: (state) => canInsert(state, nodeType),
		run: (state, dispatch, view) => {
			let content = null;

			if (
				state.selection instanceof NodeSelection &&
				state.selection.node.type == nodeType
			) {
				content = state.selection.node.content;
			}

			const el = document.body.appendChild(document.createElement('div'));

			new Vue({
				el,
				template: `
				<groepprompt :close="close" :selectgroep="selectGroep" :type="type"/>`,
				data: { type },
				methods: {
					close() {
						this.$el.remove();
					},
					selectGroep(naam, id) {
						this.$el.remove();

						view.dispatch(
							view.state.tr.replaceSelectionWith(
								nodeType.createAndFill(
									{
										type: nodeType.name,
										naam,
										id,
									},
									content
								)
							)
						);
						view.focus();
					},
				},
			});
		},
	});

export const youtubeItemPrompt = (
	nodeType: NodeType<EditorSchema>,
	label: string,
	title: string,
	description = ''
): MenuItem =>
	new MenuItem({
		title,
		label,
		enable: (state) => canInsert(state, nodeType),
		run: (state, dispatch, view) => {
			let attrs = null;
			let content = null;

			if (
				state.selection instanceof NodeSelection &&
				state.selection.node.type == nodeType
			) {
				attrs = state.selection.node.attrs;
				content = state.selection.node.content;
			}

			openPrompt({
				description,
				title: attrs
					? 'Update: ' + nodeType.name
					: 'Invoegen: ' + nodeType.name,
				fields: {
					id: new YoutubeField({
						label: 'Id',
						required: true,
						value: attrs ? attrs.id : '',
					}),
				},
				callback(callbackAttrs) {
					view.dispatch(
						view.state.tr.replaceSelectionWith(
							nodeType.createAndFill(
								{ type: nodeType.name, ...callbackAttrs },
								content
							)
						)
					);
					view.focus();
				},
			});
		},
	});

export const bbInsert = (
	nodeType: NodeType<EditorSchema>
): MenuItem<EditorSchema> =>
	new MenuItem<EditorSchema>({
		title: 'BB code als platte tekst invoegen',
		label: 'BB code',
		enable: (state) => canInsert(state, nodeType),
		run: (state, dispatch, view) => {
			let attrs: Record<string, string> = { bb: '' };

			if (
				state.selection instanceof NodeSelection &&
				state.selection.node.type == nodeType
			) {
				attrs = state.selection.node.attrs;
			}

			bbPrompt(nodeType, attrs, view);
		},
	});

export const insertPlaatjeItem = (
	nodeType: NodeType<EditorSchema>,
	imageType: NodeType<EditorSchema>
): MenuItem<EditorSchema> =>
	new MenuItem<EditorSchema>({
		title: 'Afbeelding invoegen',
		label: 'Afbeelding',
		enable: (state) =>
			canInsert(state, nodeType) || canInsert(state, imageType),
		run: (state, dispatch, view) => {
			openPrompt({
				title: 'Plaatje uploaden',
				fields: {
					noot: new Label({
						label: '',
						value:
							'Als je hier een bestand van je computer kiest is deze <strong>alleen zichtbaar voor leden</strong>. Zet je afbeelding neer bij een externe service als je wil dat je afbeelding ook op de externe stek zichtbaar is.',
					}),
					image: new FileField({ label: 'Afbeelding' }),
					of: new Label({ label: '', value: '- Of -' }),
					url: new TextField({ label: 'Url' }),
				},
				callback: (params) => {
					if (params.url) {
						view.dispatch(
							view.state.tr.replaceSelectionWith(
								imageType.createAndFill({ src: params.url })
							)
						);
						view.focus();
					} else if (params.image) {
						startImageUpload(view, params.image);
					}
				},
			});
		},
	});

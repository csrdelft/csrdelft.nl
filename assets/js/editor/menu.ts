import {
	blockTypeItem,
	Dropdown,
	DropdownSubmenu,
	MenuItem,
	wrapItem,
} from 'prosemirror-menu';
import { EditorSchema } from './schema';
import { redo, undo } from 'prosemirror-history';
import {
	bbInsert,
	blockTypeItemPrompt,
	canInsert,
	groepPrompt,
	insertCitaat,
	insertImageItem,
	insertPlaatjeItem,
	lidInsert,
	linkItem,
	markItem,
	priveItem,
	wrapListItem,
	youtubeItemPrompt,
} from './menu-item';
import { cut } from '../lib/util';
import icon from './icon';
import { joinUp, lift, selectParentNode } from 'prosemirror-commands';

/**
 * Het menu voor de prosemirror editor, op basis van de waarde van loggedIn worden specifieke
 * velden die alleen interessant zijn voor ingelogde gebruikers weergegeven.
 * @param schema
 * @param loggedIn
 */
export function buildMenuItems(
	schema: EditorSchema,
	loggedIn: boolean
): (MenuItem | Dropdown)[][] {
	return [
		cut([
			markItem(schema.marks.strong, {
				title: 'Schakel dikgedrukt',
				icon: icon.strong,
			}),
			markItem(schema.marks.em, {
				title: 'Schakel schuingedrukt',
				icon: icon.em,
			}),
			markItem(schema.marks.underline, {
				title: 'Schakel onderlijn',
				icon: icon.underline,
			}),
			linkItem(schema.marks.link),
			!loggedIn &&
				markItem(schema.marks.prive, { title: 'Maak prive', icon: icon.prive }),
			new Dropdown(
				cut([
					markItem(schema.marks.code, { title: 'Schakel code', label: 'Code' }),
					markItem(schema.marks.superscript, {
						title: 'Schakel superscript',
						label: 'Superscript',
					}),
					markItem(schema.marks.subscript, {
						title: 'Schakel subscript',
						label: 'Subscript',
					}),
					markItem(schema.marks.strikethrough, {
						title: 'Schakel doorstreep',
						label: 'Doorstreep',
					}),
					markItem(schema.marks.offtopic, {
						title: 'Schakel offtopic',
						label: 'Offtopic',
					}),
					loggedIn && priveItem(schema.marks.prive),
				]),
				{ label: 'Meer' }
			),
		]),
		cut([loggedIn && lidInsert(schema.nodes.lid)]),
		cut([
			new Dropdown(
				cut([
					loggedIn &&
						insertPlaatjeItem(schema.nodes.plaatje, schema.nodes.image),
					!loggedIn && insertImageItem(schema.nodes.image),
					loggedIn && insertCitaat(schema.nodes.citaat),
					wrapItem(schema.nodes.blockquote, {
						title: 'Maak een quote',
						label: 'Quote',
					}),
					loggedIn &&
						groepPrompt(
							schema.nodes.activiteit,
							'Activiteitenketzer',
							'Activiteit invoegen',
							'activiteiten'
						),
					loggedIn &&
						groepPrompt(
							schema.nodes.ketzer,
							'Aanschafketzer',
							'Ketzer invoegen',
							'ketzers'
						),
					loggedIn &&
						new DropdownSubmenu(
							[
								blockTypeItemPrompt(
									schema.nodes.twitter,
									'Twitter',
									'Twitter invoegen'
								),
								youtubeItemPrompt(
									schema.nodes.youtube,
									'YouTube',
									'YouTube invoegen'
								),
								blockTypeItemPrompt(
									schema.nodes.spotify,
									'Spotify',
									'Spotify invoegen'
								),
								blockTypeItemPrompt(
									schema.nodes.video,
									'Video',
									'Video invoegen'
								),
								blockTypeItemPrompt(
									schema.nodes.audio,
									'Geluid',
									'Geluid invoegen'
								),
							],
							{ label: 'Embed' }
						),
					loggedIn &&
						new DropdownSubmenu(
							[
								groepPrompt(
									schema.nodes.bestuur,
									'Bestuur',
									'Bestuur invoegen',
									'besturen'
								),
								groepPrompt(
									schema.nodes.commissie,
									'Commissie',
									'Commissie invoegen',
									'commissies'
								),
								groepPrompt(
									schema.nodes.groep,
									'Overig',
									'Overige groep invoegen',
									'overig'
								),
								groepPrompt(
									schema.nodes.ondervereniging,
									'Ondervereniging',
									'Ondervereniging invoegen',
									'onderverenigingen'
								),
								groepPrompt(
									schema.nodes.verticale,
									'Verticale',
									'Verticale invoegen',
									'verticalen'
								),
								groepPrompt(
									schema.nodes.werkgroep,
									'Werkgroep',
									'Werkgroep invoegen',
									'werkgroepen'
								),
								groepPrompt(
									schema.nodes.woonoord,
									'Woonoord',
									'Woonoord invoegen',
									'woonoorden'
								),
							],
							{ label: 'Groep' }
						),
					loggedIn &&
						blockTypeItemPrompt(schema.nodes.boek, 'Boek', 'Boek invoegen'),
					loggedIn &&
						blockTypeItemPrompt(
							schema.nodes.document,
							'Document',
							'Document invoegen'
						),
					loggedIn &&
						blockTypeItemPrompt(
							schema.nodes.maaltijd,
							'Maaltijd',
							'Maaltijd invoegen'
						),
					loggedIn &&
						blockTypeItemPrompt(
							schema.nodes.peiling,
							'Peiling',
							'Peiling invoegen'
						),
					new MenuItem({
						title: 'Horizontale lijn invoegen',
						label: 'Lijn',
						enable(state) {
							return canInsert(state, schema.nodes.horizontal_rule);
						},
						run(state, dispatch) {
							dispatch(
								state.tr.replaceSelectionWith(
									schema.nodes.horizontal_rule.create()
								)
							);
						},
					}),
					wrapItem(schema.nodes.verklapper, {
						title: 'Stop selectie in verklapper',
						label: 'Verklapper',
					}),
					bbInsert(schema.nodes.bb),
				]),
				{ label: 'Invoegen' }
			),
			new Dropdown(
				[
					blockTypeItem(schema.nodes.paragraph, {
						title: 'Verander naar een paragraaf',
						label: 'Tekst',
					}),
					blockTypeItem(schema.nodes.code_block, {
						title: 'Verander naar een code blok',
						label: 'Code',
					}),
					blockTypeItem(schema.nodes.heading, {
						title: 'Verander naar kop 1',
						label: 'Titel',
						attrs: { level: 1 },
					}),
					blockTypeItem(schema.nodes.heading, {
						title: 'Verander naar kop 2',
						label: 'Kop',
						attrs: { level: 2 },
					}),
					blockTypeItem(schema.nodes.heading, {
						title: 'Verander naar kop 3',
						label: 'Subkop',
						attrs: { level: 3 },
					}),
				],
				{ label: 'Stijl' }
			),
		]),
		[
			new MenuItem({
				title: 'Laatste wijziging ongedaan maken',
				run: undo,
				enable: (state) => undo(state),
				icon: icon.undo,
			}),
			new MenuItem({
				title: 'Herhaal de laatste ongedaan gemaakte wijziging',
				run: redo,
				enable: (state) => redo(state),
				icon: icon.redo,
			}),
		],
		[
			wrapListItem(schema.nodes.bullet_list, {
				title: 'Maak een puntenlijst',
				icon: icon.ul,
			}),
			wrapListItem(schema.nodes.ordered_list, {
				title: 'Maak een geordende lijst',
				icon: icon.ol,
			}),
			new MenuItem({
				title: 'Voeg samen met blok hier boven',
				run: joinUp,
				select: (state) => joinUp(state),
				icon: icon.join,
			}),
			new MenuItem({
				title: 'Til uit omvattende blok',
				run: lift,
				select: (state) => lift(state),
				icon: icon.lift,
			}),
			new MenuItem({
				title: 'Selecteer dit blok',
				run: selectParentNode,
				select: (state) => selectParentNode(state),
				icon: icon.selectParentNode,
			}),
		],
	];
}

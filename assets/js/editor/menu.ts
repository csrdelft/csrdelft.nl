import {
	blockTypeItem,
	Dropdown,
	DropdownSubmenu,
	icons,
	joinUpItem,
	liftItem,
	MenuItem,
	selectParentNodeItem,
	wrapItem
} from "prosemirror-menu"
import {EditorSchema} from "./schema";
import {redo, undo} from "prosemirror-history";
import {
	bbInsert,
	blockTypeHead,
	blockTypeItemPrompt,
	canInsert,
	insertImageItem,
	insertPlaatjeItem,
	linkItem,
	markItem,
	priveItem,
	wrapListItem
} from "./menu-item";


export function buildMenuItems(schema: EditorSchema): (MenuItem | Dropdown)[][] {
	return [
		[
			markItem(schema.marks.strong, {title: "Schakel dikgedrukt", icon: icons.strong}),
			markItem(schema.marks.em, {title: "Schakel schuingedrukt", icon: icons.em}),
			linkItem(schema.marks.link),
			new Dropdown([
				markItem(schema.marks.code, {title: "Schakel code", label: "Code"}),
				markItem(schema.marks.neuzen, {title: "Schakel neuzen", label: "Neuzen"}),
				markItem(schema.marks.superscript, {title: "Schakel superscript", label: "Superscript"}),
				markItem(schema.marks.subscript, {title: "Schakel subscript", label: "Subscript"}),
				markItem(schema.marks.underline, {title: "Schakel onderlijn", label: "Onderlijn"}),
				markItem(schema.marks.strikethrough, {title: "Schakel doorstreep", label: "Doorstreep"}),
				markItem(schema.marks.offtopic, {title: "Schakel offtopic", label: "Offtopic"}),
				priveItem(schema.marks.prive),
			], {label: "Meer"})
		],
		[
			blockTypeItemPrompt(schema.nodes.lid, "Lid", "Lid invoegen"),
			new Dropdown([
				insertPlaatjeItem(schema.nodes.plaatje),
				insertImageItem(schema.nodes.image),
				blockTypeItemPrompt(schema.nodes.citaat, "Citaat", "Citaat invoegen"),
				new DropdownSubmenu([
					blockTypeItemPrompt(schema.nodes.twitter, "Twitter", "Twitter invoegen"),
					blockTypeItemPrompt(schema.nodes.youtube, "YouTube", "YouTube invoegen"),
					blockTypeItemPrompt(schema.nodes.spotify, "Spotify", "Spotify invoegen"),
					blockTypeItemPrompt(schema.nodes.video, "Video", "Video invoegen"),
					blockTypeItemPrompt(schema.nodes.audio, "Geluid", "Geluid invoegen"),
				], {label: "Embed"}),
				new DropdownSubmenu([
					blockTypeItemPrompt(schema.nodes.activiteit, "Activiteit", "Activiteit invoegen"),
					blockTypeItemPrompt(schema.nodes.bestuur, "Bestuur", "Bestuur invoegen"),
					blockTypeItemPrompt(schema.nodes.commissie, "Commissie", "Commissie invoegen"),
					blockTypeItemPrompt(schema.nodes.groep, "Groep", "Groep invoegen"),
					blockTypeItemPrompt(schema.nodes.ketzer, "Ketzer", "Ketzer invoegen"),
					blockTypeItemPrompt(schema.nodes.ondervereniging, "Ondervereniging", "Ondervereniging invoegen"),
					blockTypeItemPrompt(schema.nodes.verticale, "Verticale", "Verticale invoegen"),
					blockTypeItemPrompt(schema.nodes.werkgroep, "Werkgroep", "Werkgroep invoegen"),
					blockTypeItemPrompt(schema.nodes.woonoord, "Woonoord", "Woonoord invoegen"),
				], {label: "Groep"}),
				blockTypeItemPrompt(schema.nodes.boek, "Boek", "Boek invoegen"),
				blockTypeItemPrompt(schema.nodes.document, "Document", "Document invoegen"),
				blockTypeItemPrompt(schema.nodes.maaltijd, "Maaltijd", "Maaltijd invoegen"),
				new MenuItem({
					title: "Insert horizontal rule",
					label: "Horizontal rule",
					enable(state) {
						return canInsert(state, schema.nodes.horizontal_rule)
					},
					run(state, dispatch) {
						dispatch(state.tr.replaceSelectionWith(schema.nodes.horizontal_rule.create()))
					}
				}),
				wrapItem(schema.nodes.verklapper, {
					title: "Stop selectie in verklapper",
					label: "Verklapper"
				}),
				bbInsert(schema.nodes.bb),
			], {label: "Invoegen"}),
			new Dropdown([
				blockTypeItem(schema.nodes.paragraph, {
					title: "Change to paragraph",
					label: "Plain"
				}),
				blockTypeItem(schema.nodes.code_block, {
					title: "Change to code block",
					label: "Code"
				}),
				new DropdownSubmenu([
					blockTypeHead(schema.nodes.heading, 1),
					blockTypeHead(schema.nodes.heading, 2),
					blockTypeHead(schema.nodes.heading, 3),
					blockTypeHead(schema.nodes.heading, 4),
					blockTypeHead(schema.nodes.heading, 5),
					blockTypeHead(schema.nodes.heading, 6),
				], {label: "Heading"})
			], {label: "Type..."}),
		],
		[
			new MenuItem({
				title: "Undo last change",
				run: undo,
				enable: state => undo(state),
				icon: icons.undo
			}),
			new MenuItem({
				title: "Redo last undone change",
				run: redo,
				enable: state => redo(state),
				icon: icons.redo
			})
		],
		[
			wrapListItem(schema.nodes.bullet_list, {
				title: "Wrap in bullet list",
				icon: icons.bulletList
			}),
			wrapListItem(schema.nodes.ordered_list, {
				title: "Wrap in ordered list",
				icon: icons.orderedList
			}),
			wrapItem(schema.nodes.blockquote, {
				title: "Wrap in block quote",
				icon: icons.blockquote
			}),
			joinUpItem,
			liftItem,
			selectParentNodeItem
		],
	]
}

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
import {blocks, EditorSchema} from "./schema";
import {redo, undo} from "prosemirror-history";
import {
	bbInsert,
	blockMenuItem,
	blockTypeHead,
	blockTypeItemPrompt,
	canInsert,
	insertImageItem, insertPlaatjeItem,
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
			new Dropdown([
				insertPlaatjeItem(schema.nodes.plaatje),
				insertImageItem(schema.nodes.image),
				blockTypeItemPrompt(schema.nodes.citaat, {title: "Citaat invoegen", label: "Citaat"}),
				new DropdownSubmenu([
					blockTypeItemPrompt(schema.nodes.twitter, {title: "Twitter invoegen", label: "Twitter"}),
					blockTypeItemPrompt(schema.nodes.youtube, {title: "YouTube invoegen", label: "YouTube"}),
					blockTypeItemPrompt(schema.nodes.spotify, {title: "Spotify invoegen", label: "Spotify"}),
					blockTypeItemPrompt(schema.nodes.video, {title: "Video invoegen", label: "Video"}),
					blockTypeItemPrompt(schema.nodes.audio, {title: "Geluid invoegen", label: "Geluid"}),
				], {label: "Embed"}),
				...Object.entries(blocks).map(([name, fields]) => blockMenuItem(name, fields)),
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

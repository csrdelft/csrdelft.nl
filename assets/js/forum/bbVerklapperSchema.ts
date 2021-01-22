import {NodeSpec, Schema} from "prosemirror-model";
import {wrapItem} from "prosemirror-menu";

export const bbVerklapperSpec: NodeSpec = {
	content: "block+",
	group: "block",
	defining: true,
	toDOM: () => ["div", {
		"data-bb-verklapper": "",
		class: "pm-verklapper",
	}, 0],
	parseDOM: [{tag: "div[data-bb-verklapper]"}]
}

export const addBbVerklapper = <T extends { addBefore: (before: string, name: string, spec: NodeSpec) => T }>(nodes: T): T =>
	nodes.addBefore("image", "verklapper", bbVerklapperSpec)

export const buildBbVerklapperMenu = (schema: Schema, menu: Record<string, any>): Record<string, any> => {
	menu.insertMenu.content.push(wrapItem(schema.nodes.verklapper, {
		title: "Stop selectie in verklapper",
		label: "Verklapper"
	}));

	return menu
}

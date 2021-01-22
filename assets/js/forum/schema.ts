import {NodeSpec} from "prosemirror-model";

export const blocks = {
	"groep": ["id"],
	"activiteit": ["id"]
}

export const bbBlockSpec: NodeSpec = {
	attrs: {type: {default: "groep"}, id: {default: "none"}},
	inline: false,
	group: "block",
	draggable: true,
	toDOM: node => ["div", {
		"data-bb-block-type": node.attrs.type,
		class: `pm-block pm-block-${node.attrs.type}`,
		title: node.attrs.type,
	}, `${node.attrs.type}: ${node.attrs.id}`],
	parseDOM: [{
		tag: "div[data-block-type]",
		getAttrs: (dom: HTMLElement) => {
			const type = dom.dataset.bbBlockType
			return type in blocks ? {type} : false
		}
	}]
}


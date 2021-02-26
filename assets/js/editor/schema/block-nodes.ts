import {NodeSpec} from "prosemirror-model";

const createBlockSpec = (type: string, attr = 'id'): NodeSpec => ({
	attrs: {[attr]: {}},
	group: "block",
	draggable: true,
	toDOM: node => ["div", {[`data-${type}`]: node.attrs[attr], class: "pm-block"}, `${type}: ${node.attrs[attr]}`],
	parseDOM: [{tag: `div[data-${type}`, getAttrs: (dom: HTMLElement) => ({[attr]: dom.dataset[type]})}],
})

// Groepen
export const activiteit = createBlockSpec("activiteit")
export const bestuur = createBlockSpec("bestuur")
export const commissie = createBlockSpec("commissie")
export const groep = createBlockSpec("groep")
export const ketzer = createBlockSpec("ketzer")
export const ondervereniging = createBlockSpec("ondervereniging")
export const verticale = createBlockSpec("verticale")
export const werkgroep = createBlockSpec("ondervereniging")
export const woonoord = createBlockSpec("woonoord")

// Overig
export const boek = createBlockSpec("boek")
export const document = createBlockSpec("document")
export const maaltijd = createBlockSpec("maaltijd")

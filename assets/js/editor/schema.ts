import {DOMOutputSpec, MarkSpec, Node, NodeSpec, Schema} from "prosemirror-model"

// Helper functie om typescript te laten snappen dat alle elementen van i U zijn, maar dat de sleutels nog te ontdekken zijn.
const RecordWithType = <U>() => <T extends Record<string, U>>(id: T) => id

export const blocks: Record<string, string[]> = {
	groep: ["id"],
	activiteit: ["id"],
	ishetal: [],
	maaltijd: ["id"],
	document: ["id"],
	fotoalbum: ["url"],
	foto: ["url"],
	peiling: ["url"],
	locatie: ["locatie"]
}

// :: Object
// [Specs](#model.NodeSpec) for the nodes defined in this schema.
export const nodes = RecordWithType<NodeSpec>()({
	// :: NodeSpec The top level document node.
	doc: {
		content: "block+"
	},

	// :: NodeSpec A plain paragraph textblock. Represented in the DOM
	// as a `<p>` element.
	paragraph: {
		content: "inline*",
		group: "block",
		parseDOM: [{tag: "p"}],
		toDOM: () => ["p", 0]
	},

	// :: NodeSpec A blockquote (`<blockquote>`) wrapping one or more blocks.
	blockquote: {
		content: "block+",
		group: "block",
		defining: true,
		parseDOM: [{tag: "blockquote"}],
		toDOM: () => ["blockquote", 0]
	},

	citaat: {
		attrs: {van: {default: ""}, url: {default: ""}},
		content: "block+",
		group: "block",
		defining: true,
		parseDOM: [{
			tag: "div[data-bb-citaat]",
			getAttrs: (dom: HTMLElement) => ({van: dom.dataset.bbCitaat, url: dom.dataset.bbCitaatUrl})
		}],
		toDOM: node => ["div", {"data-bb-citaat": node.attrs.van, "data-bb-citaat-url": node.attrs.url}, 0]
	},

	lid: {
		attrs: {uid: {default: null}, naam: {default: null}},
		group: "inline",
		inline: true,
		parseDOM: [{tag: "span[data-lid]", getAttrs: (dom: HTMLElement) => ({uid: dom.dataset.lid, naam: dom.dataset.lidNaam})}],
		toDOM: node => ["span", {"data-lid": node.attrs.uid, "data-lidNaam": node.attrs.naam}, node.attrs.naam],
	},

	// :: NodeSpec A horizontal rule (`<hr>`).
	horizontal_rule: {
		group: "block",
		parseDOM: [{tag: "hr"}],
		toDOM: () => ["hr"]
	},

	// :: NodeSpec A heading textblock, with a `level` attribute that
	// should hold the number 1 to 6. Parsed and serialized as `<h1>` to
	// `<h6>` elements.
	heading: {
		attrs: {level: {default: 1}},
		content: "inline*",
		group: "block",
		defining: true,
		parseDOM: [{tag: "h1", attrs: {level: 1}},
			{tag: "h2", attrs: {level: 2}},
			{tag: "h3", attrs: {level: 3}},
			{tag: "h4", attrs: {level: 4}},
			{tag: "h5", attrs: {level: 5}},
			{tag: "h6", attrs: {level: 6}}],
		toDOM: node => ["h" + node.attrs.level, 0]
	},

	// :: NodeSpec A code listing. Disallows marks or non-text inline
	// nodes by default. Represented as a `<pre>` element with a
	// `<code>` element inside of it.
	code_block: {
		content: "text*",
		marks: "",
		group: "block",
		code: true,
		defining: true,
		parseDOM: [{tag: "pre", preserveWhitespace: "full"}],
		toDOM: () => ["pre", ["code", 0]]
	},

	// :: NodeSpec The text node.
	text: {
		group: "inline"
	},

	// :: NodeSpec An inline image (`<img>`) node. Supports `src`,
	// `alt`, and `href` attributes. The latter two default to the empty
	// string.
	image: {
		inline: true,
		attrs: {
			src: {},
			alt: {default: null},
			title: {default: null}
		},
		group: "inline",
		draggable: true,
		parseDOM: [{
			tag: "img[src]",
			getAttrs: dom => {
				if (dom instanceof HTMLElement) {
					return {
						src: dom.getAttribute("src"),
						title: dom.getAttribute("title"),
						alt: dom.getAttribute("alt")
					}
				} else {
					throw new Error("Unable to process dom")
				}
			}
		}],
		toDOM: (node: Node) => {
			const {src, alt, title} = node.attrs;
			return ["img", {src, alt, title}]
		}
	},

	plaatje: {
		inline: true,
		attrs: {
			src: {},
			key: {},
		},
		group: "inline",
		draggable: true,
		parseDOM: [{
			tag: "[data-plaatje]",
			getAttrs: (dom: HTMLElement) => ({key: dom.dataset.plaatje, src: dom.dataset.plaatjeSrc})
		}],
		toDOM: (node: Node) => ["div", {"data-plaatje": node.attrs.key, "data-plaatje-src": node.attrs.src}, ["img", {src: node.attrs.src}]],
	},

	// :: NodeSpec A hard line break, represented in the DOM as `<br>`.
	hard_break: {
		inline: true,
		group: "inline",
		selectable: false,
		parseDOM: [{tag: "br"}],
		toDOM: () => ["br"]
	},

	verklapper: {
		content: "block+",
		group: "block",
		parseDOM: [{tag: "div[data-bb-verklapper]"}],
		toDOM: () => ["div", {"data-bb-verklapper": "", class: "pm-verklapper"}, 0]
	},

	"bb-block": {
		attrs: {type: {default: "groep"}, id: {default: "none"}},
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
	},

	bb: {
		attrs: {bb: {default: null}},
		group: "block",
		toDOM: node => ["div", {"data-bb": node.attrs.bb}, node.attrs.bb],
		parseDOM: [{
			tag: "div[data-bb]",
			getAttrs: (dom: HTMLElement) => ({bb: dom.dataset.bb})
		}]
	},

	// Blocks
	document: {
		attrs: {id: {default: "none"}},
		group: "block",
		toDOM: node => ["div", {"data-document": node.attrs.id, class: "pm-block"}, "Document: " + node.attrs.id],
		parseDOM: [{
			tag: "div[data-document]",
			getAttrs: (dom: HTMLElement) => ({id: dom.dataset.document})
		}]
	},

	// Embeds
	twitter: {
		attrs: {url: {default: null}},
		group: "block",
		parseDOM: [{
			tag: "div[data-bb-twitter]",
			getAttrs: (dom: HTMLElement) => ({
				url: dom.dataset.bbTwitter,
			})
		}],
		toDOM: node => ["div", {"data-bb-twitter": node.attrs.url}]
	},

	video: {
		attrs: {url: {default: null}},
		group: "block",
		parseDOM: [{
			tag: "div[data-bb-video]",
			getAttrs: (dom: HTMLElement) => ({
				url: dom.dataset.bbVideo
			})
		}],
		toDOM: node => ["div", {"data-bb-video": node.attrs.url}],
	},

	audio: {
		attrs: {url: {default: null}},
		group: "block",
		parseDOM: [{
			tag: "div[data-bb-audio]",
			getAttrs: (dom: HTMLElement) => ({
				url: dom.dataset.bbAudio,
			})
		}],
		toDOM: node => ["div", {"data-bb-audio": node.attrs.url}]
	},

	youtube: {
		attrs: {url: {default: null}},
		group: "block",
		parseDOM: [{tag: "div[data-bb-youtube]"}],
		toDOM: node => ["div", {"data-bb-youtube": node.attrs.url}]
	},

	spotify: {
		attrs: {url: {default: null}, formaat: {default: null}},
		group: "block",
		parseDOM: [{
			tag: "div[data-bb-spotify]",
			getAttrs: (dom: HTMLElement) => ({
				url: dom.dataset.bbSpotify,
				formaat: dom.dataset.bbSpotifyFormaat,
			})
		}],
		toDOM: node => ["div", {"data-bb-spotify": node.attrs.url, "data-bb-spotify-formaat": node.attrs.formaat}]
	},

})

export type NodeScheme = keyof typeof nodes

// :: Object [Specs](#model.MarkSpec) for the marks in the schema.
export const marks = RecordWithType<MarkSpec>()({
	// :: MarkSpec A link. Has `href` and `title` attributes. `title`
	// defaults to the empty string. Rendered and parsed as an `<a>`
	// element.
	link: {
		attrs: {
			href: {},
			title: {default: null}
		},
		inclusive: false,
		parseDOM: [{
			tag: "a[href]",
			getAttrs: dom => {
				if (dom instanceof HTMLElement) {
					return {href: dom.getAttribute("href"), title: dom.getAttribute("title")}
				} else {
					throw new Error("Cannot process dom");
				}
			}
		}],
		toDOM: node => {
			const {href, title} = node.attrs;
			return ["a", {href, title}, 0]
		}
	},

	// :: MarkSpec An emphasis mark. Rendered as an `<em>` element.
	// Has parse rules that also match `<i>` and `font-style: italic`.
	em: {
		parseDOM: [{tag: "i"}, {tag: "em"}, {style: "font-style=italic"}],
		toDOM: (): DOMOutputSpec => ["em", 0]
	},

	// :: MarkSpec A strong mark. Rendered as `<strong>`, parse rules
	// also match `<b>` and `font-weight: bold`.
	strong: {
		parseDOM: [{tag: "strong"},
			// This works around a Google Docs misbehavior where
			// pasted content will be inexplicably wrapped in `<b>`
			// tags with a font-weight normal.
			{
				tag: "b", getAttrs: node => {
					if (node instanceof HTMLElement) {
						return node.style.fontWeight != "normal" && null
					} else {
						throw new Error("Cannot process dom")
					}
				}
			},
			{
				style: "font-weight", getAttrs: value => {
					if (typeof value == 'string') {
						return /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null
					} else {
						throw new Error("Cannot process value")
					}
				}
			}],
		toDOM: () => ["strong", 0]
	},

	superscript: {
		parseDOM: [{tag: "sup"}],
		toDOM: () => ["sup", 0]
	},

	subscript: {
		parseDOM: [{tag: "sub"}],
		toDOM: () => ["sub", 0]
	},

	strikethrough: {
		parseDOM: [{tag: "del"}],
		toDOM: () => ["del", 0]
	},

	underline: {
		parseDOM: [{tag: "ins"}],
		toDOM: () => ["ins", 0]
	},

	// :: MarkSpec Code font mark. Represented as a `<code>` element.
	code: {
		parseDOM: [{tag: "code"}],
		toDOM: () => ["code", 0]
	},

	offtopic: {
		parseDOM: [{tag: "span[data-offtopic]"}],
		toDOM: () => ["span", {"data-offtopic": "", class: "offtopic"}, 0],
	},

	neuzen: {
		parseDom: [{tag: "span[data-neuzen]"}],
		toDOM: () => ["span", {"data-neuzen": ""}, 0],  // Geen implementatie nu
	},

	prive: {
		attrs: {prive: {default: null}},
		parseDOM: [{tag: "span[data-prive]"}],
		toDOM: (node) => ["span", {
			"data-prive": node.attrs.prive,
			class: "bb-prive",
			title: `Prive: ${node.attrs.prive || "P_LOGGED_IN"}`
		}, 0],
	},

})

export const schema = new Schema({nodes, marks})

export type EditorNodes = keyof typeof nodes
export type EditorMarks = keyof typeof marks
export type EditorSchema = typeof schema

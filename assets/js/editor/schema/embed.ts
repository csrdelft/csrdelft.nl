import {NodeSpec} from "prosemirror-model";

export const twitter: NodeSpec = {
	attrs: {url: {}},
	group: "block",
	parseDOM: [{
		tag: "div[data-bb-twitter]",
		getAttrs: (dom: HTMLElement) => ({
			url: dom.dataset.bbTwitter,
		})
	}],
	toDOM: node => ["div", {"data-bb-twitter": node.attrs.url}]
}

export const video: NodeSpec = {
	attrs: {url: {}},
	group: "block",
	parseDOM: [{
		tag: "div[data-bb-video]",
		getAttrs: (dom: HTMLElement) => ({
			url: dom.dataset.bbVideo
		})
	}],
	toDOM: node => ["div", {"data-bb-video": node.attrs.url}],
}

export const audio: NodeSpec = {
	attrs: {url: {}},
	group: "block",
	parseDOM: [{tag: "div[data-bb-audio]", getAttrs: (dom: HTMLElement) => ({url: dom.dataset.bbAudio})}],
	toDOM: node => ["div", {"data-bb-audio": node.attrs.url}]
}

export const youtube: NodeSpec = {
	attrs: {url: {}},
	group: "block",
	parseDOM: [{tag: "div[data-bb-youtube]"}],
	toDOM: node => ["div", {"data-bb-youtube": node.attrs.url}]
}

export const spotify: NodeSpec = {
	attrs: {url: {}, formaat: {default: "hoog"}},
	group: "block",
	parseDOM: [{
		tag: "div[data-bb-spotify]",
		getAttrs: (dom: HTMLElement) => ({url: dom.dataset.bbSpotify, formaat: dom.dataset.bbSpotifyFormaat})
	}],
	toDOM: node => ["div", {"data-bb-spotify": node.attrs.url, "data-bb-spotify-formaat": node.attrs.formaat}]
}

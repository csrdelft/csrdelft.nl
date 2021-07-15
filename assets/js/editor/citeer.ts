import {docReady, html} from "../lib/util";
import {NodeType, DOMParser} from "prosemirror-model";
import {createPopper} from "@popperjs/core";

const CITEER_DIV_ID = 'citeerDiv'

const getForumPost = (sel: Selection): HTMLElement | null => {
	if (sel.type == 'Range' && sel.rangeCount) {
		const range = sel.getRangeAt(0);
		const selContainer = range.commonAncestorContainer

		const htmlContainer = selContainer instanceof HTMLElement ? selContainer : selContainer.parentElement

		const post = htmlContainer.closest<HTMLElement>('.forum-bericht');
		if (post && (post.contains(selContainer) || post == selContainer)) {
			return post
		}
	}
	return null;
};

const getSelectionBoundingRect = (): DOMRect => {
	const sel = window.getSelection()
	if (!sel.rangeCount) {
		return new DOMRect();
	}

	const range = sel.getRangeAt(0);
	return range.getBoundingClientRect()
}

const getCiteerDiv = () => {
	const domCiteerDiv = document.getElementById(CITEER_DIV_ID);

	if (domCiteerDiv) return domCiteerDiv

	const citeerDiv = html`
		<div
			id="${CITEER_DIV_ID}"
			class="card position-absolute"
			style="display: none"
		>
			<button type="button" class="btn" data-selectie="">
				<i class="fas fa-quote-left"></i>
				Citeren
			</button>
		</div>`

	citeerDiv.querySelector('.btn').addEventListener('click', () => {
		const selection = window.getSelection()
		const forumPost = getForumPost(selection);

		if (!forumPost) return

		const {naam, uid} = forumPost.dataset

		const editor = window.currentEditor
		if (!editor) return

		const citaat: NodeType = editor.state.schema.nodes.citaat
		const parser = DOMParser.fromSchema(editor.state.schema)

		const parsedContent = parser.parse(selection.getRangeAt(0).cloneContents());

		const citaatNode = citaat.create({van: uid, naam}, parsedContent.content);

		editor.dispatch(editor.state.tr.insert(editor.state.selection.head, citaatNode))

		$(window).scrollTo('#reageren');

		citeerDiv.style.display = 'none';
		// We returnen altijd false, dan wordt de href= van <a> niet meer uitgevoerd.
		// Het werkt dan dus nog wel als javascript uit staat.
		return false;
	})

	return document.body.appendChild(citeerDiv);
}

/**
 * Laat citeerDiv zien bij een selectie.
 */
const citeerSelectionHandler = () => {
	const citeerDiv = getCiteerDiv()
	const selectionInForumPost = getForumPost(window.getSelection());

	if (window.currentEditor && selectionInForumPost && !window.getSelection().isCollapsed) {
		citeerDiv.style.display = "block";

		const popper = createPopper({ getBoundingClientRect: getSelectionBoundingRect }, citeerDiv, {placement: 'bottom'})
		popper.update()
	} else {
		citeerDiv.style.display = "none";
	}
}

docReady(() => {
	document.addEventListener('selectionchange', citeerSelectionHandler);
})

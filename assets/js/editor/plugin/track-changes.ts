import {Plugin} from "prosemirror-state"

/**
 * Track changes as JSON to an input element
 * @param input
 */
export const trackChangesPlugin = (input: HTMLInputElement): Plugin => new Plugin({
	appendTransaction(trs, oldState, newState) {
		input.value = JSON.stringify(newState.doc.toJSON())
	}
})

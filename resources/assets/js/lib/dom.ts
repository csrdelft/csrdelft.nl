/**
 * Selecteer een element uit de dom en geef een error als deze niet bestaat.
 * @param selectors
 * @param container
 * @param bericht
 */
export const select = <T extends Element = Element>(selectors: string, container: Element | Document = document, bericht = ""): T => {
	const el = container.querySelector<T>(selectors)

	if (!el) {
		throw new Error(`Element "${selectors}" niet gevonden. ${bericht}`)
	}

	return el
}

export const selectAll = <T extends Element = Element>(selectors: string, container: Element | Document = document): NodeListOf<T> => {
	return container.querySelectorAll<T>(selectors)
}


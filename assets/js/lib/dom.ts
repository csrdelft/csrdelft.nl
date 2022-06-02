/**
 * Selecteer een element uit de dom en geef een error als deze niet bestaat.
 * @param selectors
 * @param container
 * @param bericht
 * @throws Error als element niet wordt gevonden
 */
export const select = <T extends Element = HTMLElement>(
	selectors: string,
	container: Element | Document = document,
	bericht = ''
): T => {
	const el = container.querySelector<T>(selectors);

	if (!el) {
		throw new Error(`Element "${selectors}" niet gevonden. ${bericht}`);
	}

	return el;
};

export const selectAll = <T extends Element = HTMLElement>(
	selectors: string,
	container: Element | Document = document
): T[] => {
	return Array.from(container.querySelectorAll<T>(selectors));
};

export const parents = (element: HTMLElement, selector: string | null = null): HTMLElement => {
	let parent = element.parentElement;

	if (selector) {
		while (parent && !parent.matches(selector)) {
			parent = parent.parentElement;
		}
	}

	if (!parent) {
		throw new Error('Parent verwacht');
	}

	return parent;
};

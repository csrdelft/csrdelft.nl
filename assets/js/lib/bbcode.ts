import {html, preloadImage} from "./util";

export const loadBbImage = async (el: HTMLElement): Promise<void> => {
	const {src} = el.dataset

	// prettier-ignore
	const content = html`<img
													class="bb-img"
													alt="${src}"
													style="${el.getAttribute("style")}"
													src="${src}"/>`;

	if (!src) {
		throw new Error('Bb image heeft geen src');
	}

	try {
		await preloadImage(src)

		el.replaceWith(content)
	} catch (e) {
		// prettier-ignore
		el.replaceWith(html`<div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle"></i> Afbeelding kan niet geladen worden.</div>`)
	}
};

import {selectAll} from "./dom";

/**
 * Lazyload door <noscript> blokken van de noscript tag te ontdoen.
 *
 * Door een <noscript> tag te gebruiken wordt de inhoud geladen als js uitgeschakeld is, maar niet geladen als
 * javascript is ingeschakeld. In het laatste geval zorgen we ervoor, met javascript, dat de blokken alsnog geladen
 * worden. Dit laden gebeurt nadat de rest van de pagina geladen is en zorgt ervoor dat de gebruiker snel aan de
 * slag kan gaan op de pagina.
 * @param selector
 */
export const lazyLoad = (selector: string): void => {
	const textarea = document.createElement('textarea');

	const load = () => {
		for (const element of selectAll(selector)) {
			// setTimeout om lazy-load blokken na elkaar te laden ipv allemaal tegelijk.
			setTimeout(() => {
				const innerHTML = element.innerHTML.trim();

				// Sommige browsers encoden de inhoud van de noscript tag.
				if (innerHTML.startsWith('&lt;')) {
					textarea.innerHTML = innerHTML;
					element.outerHTML = textarea.value;
				} else {
					element.outerHTML = innerHTML;
				}

				window.refreshFsLightbox();
			});
		}
	}

	if (window.scrollY === 0) {
		const listener = () => {
			load();
			document.removeEventListener('scroll', listener);
		}
		document.addEventListener('scroll', listener)
	} else {
		load();
	}
}

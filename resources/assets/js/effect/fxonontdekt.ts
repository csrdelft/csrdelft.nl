import Parallax from 'parallax-js';

import '../../sass/effect/onontdekt.scss';

export const parallax = new Parallax(document.getElementById('onontdekt-overlay')!, {
	originY: 1.0, // Als de muis helemaal onderin is, moet de onderkant van het plaatje op nul
});

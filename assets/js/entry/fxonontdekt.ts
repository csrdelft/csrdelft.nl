import Parallax from 'parallax-js';

import '../../scss/effect/onontdekt.scss';

const overlay = document.getElementById('onontdekt-overlay');

if (!overlay) {
	throw new Error("Overlay niet gevonden")
}

new Parallax(overlay, {
	originY: 1.0, // Als de muis helemaal onderin is, moet de onderkant van het plaatje op nul
});

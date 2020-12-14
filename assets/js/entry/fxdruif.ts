// importeer stijl
import '../../scss/effect/druif.scss';
import {ontstuiter} from '../lib/util';

class FxDruif {

	private static startAnimatie() {
		if (!document.body.classList.contains('gestart')) {
			//voeg deze class alleen aan specifieke druiven toe  -> class element selector oid (jquery)
			document.body.classList.add('gestart');
		}

		// target een specifieke druif met javascript code. (alleen druiven die in het scherm zichtbaar zijn).
		// voeg een timer toe om de class weer te verwijderen

		//verwijder de druif pas: als hij uit het scherm is OF , dus verplaats hem naar dynamic positie.
		var duratie = 2000;
		setTimeout(function() {
			document.body.classList.remove("gestart");
		}, duratie);
	}

	private static laadAnimatie() {
		document.body.classList.add('druif');
	}

	public start() {
		window.addEventListener('scroll', ontstuiter(FxDruif.startAnimatie, 250, true), {passive: true});
		window.addEventListener('load', ontstuiter(FxDruif.laadAnimatie, 250, true), {passive: true});
	}
}

new FxDruif().start();

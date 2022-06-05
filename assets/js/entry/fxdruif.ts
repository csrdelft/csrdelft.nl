// importeer stijl
import '../../scss/effect/druif.scss';
import { ontstuiter } from '../lib/util';

class DruifRomeinen {
	private static laadAnimatie() {
		document
			.querySelectorAll(".forumpasfoto a[href^='/profiel/20']")
			.forEach((profiel) => {
				profiel.parentElement.classList.add('druif');
				profiel.addEventListener('mouseover', () =>
					profiel.parentElement.classList.add('start-druifeffect')
				);
			});
	}

	public start() {
		window.addEventListener(
			'load',
			ontstuiter(DruifRomeinen.laadAnimatie, 250, true),
			{ passive: true }
		);
	}
}

new DruifRomeinen().start();

// importeer stijl
import '../../scss/effect/raket.scss';
import { ontstuiter } from '../lib/util';

class UVo {
	private static laadAnimatie() {
		document
			.querySelectorAll(
				".forum-draad .pasfoto-container img.pasfoto[src^='/profiel/pasfoto/19']"
			)
			.forEach((profiel) => {
				profiel.parentElement.classList.add('raket');
			});
	}

	private static startAnimatie() {
		if (!document.body.classList.contains('scrolling')) {
			document.body.classList.add('scrolling');
		}
	}

	private static stopAnimatie() {
		if (document.body.classList.contains('scrolling')) {
			document.body.classList.remove('scrolling');
		}
	}

	public start() {
		window.addEventListener('load', ontstuiter(UVo.laadAnimatie, 250, true), {
			passive: true,
		});
		window.addEventListener(
			'scroll',
			ontstuiter(UVo.startAnimatie, 250, true),
			{ passive: true }
		);
		window.addEventListener(
			'scroll',
			ontstuiter(UVo.stopAnimatie, 250, false),
			{ passive: true }
		);
	}
}

new UVo().start();

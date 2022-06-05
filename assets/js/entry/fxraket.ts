// importeer stijl
import '../../scss/effect/raket.scss';
import { ontstuiter } from '../lib/util';

class UVo {
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

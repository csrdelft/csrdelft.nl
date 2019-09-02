// importeer stijl
import '../../sass/effect/raket.scss';

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
		window.addEventListener('scroll', this.ontstuiter(UVo.startAnimatie, 250, true), {passive: true});
		window.addEventListener('scroll', this.ontstuiter(UVo.stopAnimatie, 250, false), {passive: true});
	}

	private ontstuiter(func: any, wait: number, immediate: boolean) {
		let timeout: number | undefined;
		return function () {
			const context = this;
			const args = arguments;
			const later = () => {
				timeout = undefined;
				if (!immediate) {
					func.apply(context, args);
				}
			};
			const callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = window.setTimeout(later, wait);
			if (callNow) {
				func.apply(context, args);
			}
		};
	}
}

new UVo().start();

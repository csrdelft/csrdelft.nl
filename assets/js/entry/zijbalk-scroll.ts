import { docReady } from '../lib/util';

docReady(() => {
	// Zijbalk meescrollen
	let lastHeight = 0;
	let stickTop = false;
	let stickBottom = false;
	let headerHeight;
	let zijbalkHeight;
	let windowHeight;
	const $zijbalk = $('nav#zijbalk');

	function determineSizes() {
		headerHeight = $('nav#menu').outerHeight();
		zijbalkHeight = $zijbalk.outerHeight();
		windowHeight = $(window).height();
		lastHeight = Math.max(headerHeight, lastHeight);
		if (!stickTop && !stickBottom) {
			$zijbalk.css('top', lastHeight + 'px');
		}
	}

	function setZijbalkState() {
		const scroll = $(document).scrollTop();
		const screenTop = scroll + headerHeight;
		const screenBottom = scroll + windowHeight;
		const zijbalkTop = lastHeight;
		const zijbalkBottom = lastHeight + zijbalkHeight;

		if (screenTop < zijbalkTop) {
			// Stick to top
			if (!stickTop) {
				if (stickBottom) {
					$zijbalk.removeClass('stickBottom');
					stickBottom = false;
				}
				stickTop = true;
				$zijbalk.css('top', headerHeight + 'px');
				$zijbalk.addClass('stickTop');
			}
			lastHeight = Math.max(headerHeight, screenTop);
		} else if (screenBottom > zijbalkBottom) {
			// Stick to bottom
			if (!stickBottom) {
				if (stickTop) {
					$zijbalk.removeClass('stickTop');
					stickTop = false;
				}
				stickBottom = true;
				$zijbalk.css('top', '');
				$zijbalk.addClass('stickBottom');
			}
			lastHeight = Math.max(headerHeight, screenBottom - zijbalkHeight);
		} else {
			// Scroll
			if (stickBottom) {
				$zijbalk.removeClass('stickBottom');
				stickBottom = false;
				$zijbalk.css('top', lastHeight + 'px');
			} else if (stickTop) {
				$zijbalk.removeClass('stickTop');
				stickTop = false;
				$zijbalk.css('top', lastHeight + 'px');
			}
		}
	}

	determineSizes();
	setZijbalkState();
	$(window).resize(function () {
		determineSizes();
		setZijbalkState();
	});
	$(window).scroll(function () {
		setZijbalkState();
	});
});

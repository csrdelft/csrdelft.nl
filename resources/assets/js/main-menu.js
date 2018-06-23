import $ from 'jquery';

$(function () {

	let active = null;

	function isVisible(id) {
		return active === id;
	}

	/**
	 * Zorg ervoor dat de body niet kan scrollen als de overlay zichtbaar is.
	 */
	function toggleScroll() {
		if (active === '#search' || active === '#menu') {
			$('body')
				.removeClass('overflow-x-hidden')
				.addClass('overflow-hidden');
		} else if (active === '#zijbalk') {
			$('body')
				.removeClass('overflow-hidden')
				.addClass('overflow-x-hidden');
		} else {
			// Sta toe om te scrollen _nadat_ de animatie klaar is.
			setTimeout(() => $('body').removeClass('overflow-hidden overflow-x-hidden'), 300);
		}
	}

	/**
	 * Terug naar gewone view.
	 */
	function reset() {
		active = null;

		$('.target').removeClass('target');

		toggleScroll();
	}

	/**
	 *
	 * @param id
	 */
	function view(id) {
		active = id;

		$('.target').not(id).removeClass('target');
		$(id).addClass('target');

		toggleScroll();
	}

	/**
	 * Toggle view met id.
	 * @param id
	 */
	function toggle(id) {
		return function (event) {
			event.preventDefault();
			if (active === id) {
				reset();
			} else {
				active = id;

				$('.target').not(id).removeClass('target');
				$(id).toggleClass('target');

				toggleScroll();
			}
		};
	}

	let xDown = null;
	let yDown = null;

	function handleTouchStart(evt) {
		xDown = evt.touches[0].clientX;
		yDown = evt.touches[0].clientY;
	}

	function swipeLeft() {
		if (isVisible('#zijbalk')) {
			reset();
		} else {
			view('#menu');
		}
	}

	function swipeRight() {
		if (isVisible('#menu')) {
			reset();
		} else {
			view('#zijbalk');
		}
	}

	function handleTouchMove(evt) {
		if (!xDown || !yDown) { // Geen touch gestart
			return;
		}

		let xDiff = xDown - evt.touches[0].clientX;
		let yDiff = yDown - evt.touches[0].clientY;

		if (Math.abs(xDiff) > Math.abs(yDiff)) { // Als er horizontall geswiped wordt.
			if (xDiff > 0) {
				swipeLeft();
			} else {
				swipeRight();
			}
		}
		/* reset values */
		xDown = null;
		yDown = null;
	}

	document.addEventListener('touchstart', handleTouchStart, false);
	document.addEventListener('touchmove', handleTouchMove, false);

	//open submenu
	$('.has-children').children('a').on('click', function (event) {
		event.preventDefault();
		let selected = $(this);
		if (selected.next('ul').hasClass('is-hidden')) {
			//desktop version only
			selected.addClass('selected').next('ul').removeClass('is-hidden').end().parent('.has-children').parent('ul').addClass('moves-out');
			selected.parent('.has-children').siblings('.has-children').children('ul').addClass('is-hidden').end().children('a').removeClass('selected');
		} else {
			selected.removeClass('selected').next('ul').addClass('is-hidden').end().parent('.has-children').parent('ul').removeClass('moves-out');
		}
	});

	//submenu items - go back link
	$('.go-back').on('click', function () {
		$(this).parent('ul').addClass('is-hidden').parent('.has-children').parent('ul').removeClass('moves-out');
	});

	$('.trigger[href="#menu"]').on('click', toggle('#menu'));
	$('.trigger[href="#zijbalk"]').on('click', toggle('#zijbalk'));
	$('.trigger[href="#search"]').on('click', toggle('#search'));

	$('#cd-main-overlay,.cd-main-content').on('click', reset);

	let $searchfield = $('.cd-search').find('input[type="search"]');

	// Catch keystrokes for instant search
	$(document).on('keydown', (event) => {
		// Geen instantsearch met modifiers
		if (event.ctrlKey || event.altKey || event.metaKey) {
			return;
		}

		// Geen instantsearch als we in een input-element of text-area zitten.
		let element = event.target.tagName.toUpperCase();
		if (element === 'INPUT' || element === 'TEXTAREA' || element === 'SELECT') {
			return;
		}

		// a-z en 0-9 incl. numpad
		if ((event.keyCode > 64 && event.keyCode < 91) || (event.keyCode > 47 && event.keyCode < 58) || (event.keyCode > 95 && event.keyCode < 106)) {
			$searchfield.val('');
			$searchfield.trigger('focus');
			view('#search');
		}
	});

	$(document).on('keyup', (event) => {
		if (event.keyCode === 27) { // esc
			reset();
		}
	});
});

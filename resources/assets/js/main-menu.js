import $ from 'jquery';

$(function () {
	//if you change this breakpoint in the style.css file (or _layout.scss if you use SASS), don't forget to update this value as well
	let MqL = 960;

	function checkWindowWidth() {
		//check window width (scrollbar included)
		let e = window;
		let a = 'inner';
		if (!('innerWidth' in window)) {
			a = 'client';
			e = document.documentElement || document.body;
		}
		return e[a + 'Width'] >= MqL;
	}

	function moveNavigation() {
		let navigation = $('.cd-nav');
		let desktop = checkWindowWidth();
		if (desktop) {
			navigation.detach();
			navigation.insertBefore('.cd-header-buttons');
		} else {
			navigation.detach();
			navigation.insertAfter('.cd-main-content');
		}
	}

	//move nav element position according to window width
	moveNavigation();
	$(window).on('resize', () => {
		if (!window.requestAnimationFrame) {
			setTimeout(moveNavigation, 300);
		} else {
			window.requestAnimationFrame(moveNavigation);
		}
	});

	let $overlay = $('#cd-main-overlay');

	let $maintrigger = $('#cd-main-trigger').on('click', function () {
		if ($maintrigger.hasClass('selected')) {
			$(this).addClass('nav-is-visible');
			$('.cd-primary-nav').addClass('nav-is-visible');
			$('.cd-main-header').addClass('nav-is-visible');
			$('.cd-main-content').addClass('nav-is-visible').one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', () => {
				$('body').addClass('overflow-hidden');
			});
			toggleSearch('close');
			$overlay.addClass('is-visible');
		}
	});

	//mobile - open lateral menu clicking on the menu icon
	$('.cd-nav-trigger').on('click', function (event) {
		event.preventDefault();
		if ($('.cd-main-content').hasClass('nav-is-visible')) {
			closeNav();
			$overlay.removeClass('is-visible');
		} else {
			//open main menu
			if (!$maintrigger.hasClass('selected')) {
				$maintrigger.addClass('selected');
				$maintrigger.click();
			}
		}
	});

	//open search form
	$('.cd-search-trigger').on('click', event => {
		event.preventDefault();
		closeNav();
		toggleSearch();
	});

	//close lateral menu on mobile
	$overlay.on('swiperight', () => {
		if ($('.cd-primary-nav').hasClass('nav-is-visible')) {
			closeNav();
			$overlay.removeClass('is-visible');
		}
	});
	$('.nav-on-left #cd-main-overlay').on('swipeleft', () => {
		if ($('.cd-primary-nav').hasClass('nav-is-visible')) {
			closeNav();
			$overlay.removeClass('is-visible');
		}
	});
	$overlay.on('click', () => {
		closeNav();
		toggleSearch('close');
		$overlay.removeClass('is-visible');
	});


	//prevent default clicking on direct children of .cd-primary-nav
	$('.cd-primary-nav').children('.has-children').children('a').on('click', (event) => {
		event.preventDefault();
	});
	//open submenu
	$('.has-children').children('a').on('click', function (event) {
		if (!checkWindowWidth()) {
			event.preventDefault();
		}
		let selected = $(this);
		if (selected.next('ul').hasClass('is-hidden')) {
			//desktop version only
			selected.addClass('selected').next('ul').removeClass('is-hidden').end().parent('.has-children').parent('ul').addClass('moves-out');
			selected.parent('.has-children').siblings('.has-children').children('ul').addClass('is-hidden').end().children('a').removeClass('selected');
			$overlay.addClass('is-visible');
		} else {
			selected.removeClass('selected').next('ul').addClass('is-hidden').end().parent('.has-children').parent('ul').removeClass('moves-out');
			$overlay.removeClass('is-visible');
		}
		toggleSearch('close');
	});

	//submenu items - go back link
	$('.go-back').on('click', function () {
		$(this).parent('ul').addClass('is-hidden').parent('.has-children').parent('ul').removeClass('moves-out');
	});

	function closeNav() {
		$('.cd-nav-trigger').removeClass('nav-is-visible');
		$('.cd-main-header').removeClass('nav-is-visible');
		$('.cd-primary-nav').removeClass('nav-is-visible');
		$('.has-children ul').addClass('is-hidden');
		$('.has-children a').removeClass('selected');
		$('.moves-out').removeClass('moves-out');
		$('.cd-main-content').removeClass('nav-is-visible').one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function () {
			$('body').removeClass('overflow-hidden');
		});
	}

	let $searchfield = $('.cd-search').find('input[type="search"]');

	function toggleSearch(type) {
		let $cdSearch = $('.cd-search');
		if (type === 'close') {
			$cdSearch.removeClass('is-visible');
			$('.cd-search-trigger').removeClass('search-is-visible');
		} else {
			$cdSearch.toggleClass('is-visible');
			$('.cd-search-trigger').toggleClass('search-is-visible');
		}
		if ($cdSearch.hasClass('is-visible')) {
			$searchfield.trigger('focus');
			$maintrigger.fadeOut();
			$('#cd-user-avatar').fadeOut();
			$overlay.addClass('is-visible');
		} else {
			$maintrigger.fadeIn();
			$('#cd-user-avatar').fadeIn();
			if (!$maintrigger.hasClass('selected')) {
				$overlay.removeClass('is-visible');
			}
		}
	}

	let fadeToggle = false;
	// Fade header when leaving top of page
	function toggleFade() {
		if ($(window).scrollTop() > 100) {
			$maintrigger.addClass('fade');
			$('#cd-user-avatar').addClass('fade');
		}
		else {
			$maintrigger.removeClass('fade');
			$('#cd-user-avatar').removeClass('fade');
		}
		fadeToggle = false;
	}

	$(window).on('scroll', function () {
		if (!fadeToggle) {
			fadeToggle = true;
			$maintrigger.data('timer', setTimeout(toggleFade, 3000));
		}
	}).trigger('scroll');

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
			$searchfield.focus();
			if (!$('.cd-search').hasClass('is-visible')) {
				closeNav();
				toggleSearch();
			}
		}
	});

	$searchfield.on('keyup', (event) => {
		if (event.keyCode === 27) { // esc
			toggleSearch('close');
		}
	});
});

jQuery(document).ready(function ($) {
	//if you change this breakpoint in the style.css file (or _layout.scss if you use SASS), don't forget to update this value as well
	var MqL = 960;
	//move nav element position according to window width
	moveNavigation();
	$(window).on('resize', function () {
		if (!window.requestAnimationFrame) {
			setTimeout(moveNavigation, 300);
		} else {
			window.requestAnimationFrame(moveNavigation);
		}
	});

	var $maintrigger = $('#cd-main-trigger').on('click', function (event) {
		try {
			startClouds();
		}
		catch (err) {
			// Missing js file
		}
	});

	//mobile - open lateral menu clicking on the menu icon
	$('.cd-nav-trigger').on('click', function (event) {
		event.preventDefault();
		if ($('.cd-main-content').hasClass('nav-is-visible')) {
			closeNav();
			$('.cd-main-overlay').removeClass('is-visible');
		} else {
			$(this).addClass('nav-is-visible');
			$('.cd-primary-nav').addClass('nav-is-visible');
			$('.cd-main-header').addClass('nav-is-visible');
			$('.cd-main-content').addClass('nav-is-visible').one('webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend', function () {
				$('body').addClass('overflow-hidden');
			});
			toggleSearch('close');
			$('.cd-main-overlay').addClass('is-visible');

			//open main menu
			if (!$maintrigger.hasClass('selected')) {
				$maintrigger.click();
			}
		}
	});

	//open search form
	$('.cd-search-trigger').on('click', function (event) {
		event.preventDefault();
		toggleSearch();
		closeNav();
	});

	//close lateral menu on mobile 
	$('.cd-main-overlay').on('swiperight', function () {
		if ($('.cd-primary-nav').hasClass('nav-is-visible')) {
			closeNav();
			$('.cd-main-overlay').removeClass('is-visible');
		}
	});
	$('.nav-on-left .cd-main-overlay').on('swipeleft', function () {
		if ($('.cd-primary-nav').hasClass('nav-is-visible')) {
			closeNav();
			$('.cd-main-overlay').removeClass('is-visible');
		}
	});
	$('.cd-main-overlay').on('click', function () {
		closeNav();
		toggleSearch('close');
		$('.cd-main-overlay').removeClass('is-visible');
	});


	//prevent default clicking on direct children of .cd-primary-nav 
	$('.cd-primary-nav').children('.has-children').children('a').on('click', function (event) {
		event.preventDefault();
	});
	//open submenu
	$('.has-children').children('a').on('click', function (event) {
		if (!checkWindowWidth())
			event.preventDefault();
		var selected = $(this);
		if (selected.next('ul').hasClass('is-hidden')) {
			//desktop version only
			selected.addClass('selected').next('ul').removeClass('is-hidden').end().parent('.has-children').parent('ul').addClass('moves-out');
			selected.parent('.has-children').siblings('.has-children').children('ul').addClass('is-hidden').end().children('a').removeClass('selected');
			$('.cd-main-overlay').addClass('is-visible');
		} else {
			selected.removeClass('selected').next('ul').addClass('is-hidden').end().parent('.has-children').parent('ul').removeClass('moves-out');
			$('.cd-main-overlay').removeClass('is-visible');
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
		try {
			stopClouds();
		}
		catch (err) {
			// Missing js file
		}
	}

	function checkWindowWidth() {
		//check window width (scrollbar included)
		var e = window;
		var a = 'inner';
		if (!('innerWidth' in window)) {
			a = 'client';
			e = document.documentElement || document.body;
		}
		if (e[ a + 'Width' ] >= MqL) {
			return true;
		} else {
			return false;
		}
	}

	function moveNavigation() {
		var navigation = $('.cd-nav');
		var desktop = checkWindowWidth();
		if (desktop) {
			navigation.detach();
			navigation.insertBefore('.cd-header-buttons');
		} else {
			navigation.detach();
			navigation.insertAfter('.cd-main-content');
		}
	}

	var $searchfield = $('.cd-search').find('input[type="search"]');

	function toggleSearch(type) {
		if (type === 'close') {
			//close serach 
			$('.cd-search').removeClass('is-visible');
			$('.cd-search-trigger').removeClass('search-is-visible');
			$maintrigger.fadeIn();
		} else {
			//toggle search visibility
			$('.cd-search').toggleClass('is-visible');
			$('.cd-search-trigger').toggleClass('search-is-visible');
			if ($('.cd-search').hasClass('is-visible')) {
				$searchfield.focus();
				$maintrigger.fadeOut();
			} else {
				$maintrigger.fadeIn();
			}
		}
	}

	// Catch keystrokes for instant search
	$(document).keydown(function (event) {

		// Geen instantsearch met modifiers
		if (bCtrlPressed || bAltPressed || bMetaPressed) {
			return;
		}

		// Geen instantsearch als we in een input-element of text-area zitten.
		var element = event.target.tagName.toUpperCase();
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

	$searchfield.keyup(function (event) {
		if (event.keyCode === 27) { // esc
			toggleSearch('close');
		}
	});
});
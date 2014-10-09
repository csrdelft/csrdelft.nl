
var bShiftPressed = false;
var bCtrlPressed = false;
var bAltPressed = false;
var bMetaPressed = false;

/**
 * Houdt bij welke modifier keys worden ingedrukt.
 * Werkt dankzij jQuery voor Mac & PC hetzelfde.
 * 
 * Slimme keydown constructie vanwege repeated
 * calls op die functie zolang er een toets wordt
 * ingedrukt.
 */
function init_key_pressed() {
	$(window).on('keyup', function (event) {
		$(window).one('keydown', function (event) {
			key_pressed_update(event);
		});
		key_pressed_update(event);
	});
	$(window).trigger('keyup');
}

function key_pressed_update(event) {
	bShiftPressed = (event.shiftKey ? true : false);
	bCtrlPressed = (event.ctrlKey ? true : false);
	bAltPressed = (event.altKey ? true : false);
	bMetaPressed = (event.metaKey ? true : false);
}



jQuery(document).ready(function ($) {

	init_key_pressed();

	var $ingelogd_menu_trigger = $('#cd-ingelogd-menu-trigger');
	var $ingelogd_menu = $('#cd-ingelogd-menu');
	var $lateral_menu_trigger = $('#cd-lateral-menu-trigger');
	var $lateral_menu = $('#cd-lateral-nav');
	var $content_wrapper = $('.cd-main-content');
	var $header = $('header');

	// Close ingelogd menu
	var close_ingelogd_menu = function () {
		$ingelogd_menu_trigger.removeClass('ingelogd-menu-is-open');
		$ingelogd_menu.slideUp(200);
	};

	// Close lateral menu
	var close_lateral_menu = function () {
		$lateral_menu_trigger.removeClass('is-clicked');
		$header.removeClass('lateral-menu-is-open');
		$content_wrapper.removeClass('lateral-menu-is-open');
		$lateral_menu.removeClass('lateral-menu-is-open');

		// Clear value and de-focus instant search field
		$('#cd-zoek-veld').val('').blur();
	};

	// Open ingelogd menu on hover over trigger
	var open_ingelogd_menu = function () {
		//close_lateral_menu();

		$ingelogd_menu_trigger.addClass('ingelogd-menu-is-open');
		$ingelogd_menu.slideDown(200);
	};
	//$ingelogd_menu_trigger.hoverIntent(open_ingelogd_menu, function () {});

	// Open lateral menu on hover over trigger
	var open_lateral_menu = function () {
		//close_ingelogd_menu();

		$lateral_menu_trigger.addClass('is-clicked');
		$header.addClass('lateral-menu-is-open');
		$content_wrapper.addClass('lateral-menu-is-open');
		$lateral_menu.addClass('lateral-menu-is-open');
	};
	//$lateral_menu_trigger.hoverIntent(open_lateral_menu, function () {});

	// Toggle ingelogd menu clicking on the trigger
	$ingelogd_menu_trigger.on('click', function (event) {
		if (!$(event.target).is('#cd-ingelogd-menu a, #cd-ingelogd-menu a span')) {
			event.preventDefault();

			if ($ingelogd_menu_trigger.hasClass('ingelogd-menu-is-open')) {
				close_ingelogd_menu();
			} else {
				open_ingelogd_menu();
			}
		}
	});

	// Toggle lateral menu clicking on the trigger
	$lateral_menu_trigger.on('click', function (event) {
		event.preventDefault();

		if ($lateral_menu_trigger.hasClass('is-clicked')) {
			close_lateral_menu();
		} else {
			open_lateral_menu();
		}
	});

	// Close all menus clicking outside the menu itself
	$content_wrapper.on('click', function (event) {
		if (!$(event.target).is('#cd-ingelogd-menu-trigger, #cd-ingelogd-menu-trigger span, #cd-lateral-menu-trigger, #cd-lateral-menu-trigger span')) {
			// Close menus
			close_ingelogd_menu();
			close_lateral_menu();
		}
	});

	// Open or close submenu items in the lateral menu and close all the other open submenu items
	$('.item-has-children').children('a').on('click', function (event) {
		event.preventDefault();

		$(this).toggleClass('sub-menu-open').next('.sub-menu').slideToggle(200).end().parent('.item-has-children').siblings('.item-has-children').children('a').removeClass('sub-menu-open').next('.sub-menu').slideUp(200);
	});

	// Catch keystrokes for instant search
	$(document).keydown(function (event) {

		// Geen instantsearch met modifiers
		if (bShiftPressed || bCtrlPressed || bAltPressed || bMetaPressed) {
			return;
		}

		// Geen instantsearch als we in een input-element of text-area zitten.
		var element = event.target.tagName.toUpperCase();
		if (element == 'INPUT' || element == 'TEXTAREA' || element == 'SELECT') {
			return;
		}

		// a-z en 0-9 incl. numpad
		if ((event.keyCode > 64 && event.keyCode < 91) || (event.keyCode > 47 && event.keyCode < 58) || (event.keyCode > 95 && event.keyCode < 106)) {
			$('#cd-zoek-veld').focus();
			open_lateral_menu();
		}
	});

	$('#cd-zoek-veld').keyup(function (event) {
		if (event.keyCode === 27) { // esc
			close_lateral_menu();
		}
	});
});
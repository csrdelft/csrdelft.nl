
jQuery(document).ready(function ($) {

	var $ingelogd_menu_toggle = $('#cd-ingelogd-menu-toggle');
	var $ingelogd_menu = $('#cd-ingelogd-menu');
	var $lateral_menu_toggle = $('#cd-lateral-menu-toggle');
	var $lateral_menu = $('#cd-lateral-nav');
	var $content_wrapper = $('.cd-main-content');
	var $header = $('header');

	// Close ingelogd menu
	var close_ingelogd_menu = function () {
		$ingelogd_menu_toggle.removeClass('ingelogd-menu-is-open');
		$ingelogd_menu.slideUp(200);
	};

	// Close lateral menu
	var close_lateral_menu = function () {
		$lateral_menu_toggle.removeClass('is-clicked');
		$header.removeClass('lateral-menu-is-open');
		$content_wrapper.removeClass('lateral-menu-is-open');
		$lateral_menu.removeClass('lateral-menu-is-open');

		// Clear value and de-focus instant search field
		$('#cd-zoek-form').find('.menuzoekveld').val('').blur();
	};

	// Open ingelogd menu on hover over trigger
	var open_ingelogd_menu = function () {
		//close_lateral_menu();

		$ingelogd_menu_toggle.addClass('ingelogd-menu-is-open');
		$ingelogd_menu.slideDown(200);
	};

	// Open lateral menu on hover over trigger
	var open_lateral_menu = function () {
		//close_ingelogd_menu();

		$lateral_menu_toggle.addClass('is-clicked');
		$header.addClass('lateral-menu-is-open');
		$content_wrapper.addClass('lateral-menu-is-open');
		$lateral_menu.addClass('lateral-menu-is-open');
	};
	//$lateral_menu_trigger.hoverIntent(open_lateral_menu, function () {});

	// Toggle ingelogd menu clicking on the trigger
	$ingelogd_menu_toggle.on('click', function (event) {
		if (!$(event.target).is('#cd-ingelogd-menu a, #cd-ingelogd-menu a span')) {
			event.preventDefault();

			if ($ingelogd_menu_toggle.hasClass('ingelogd-menu-is-open')) {
				close_ingelogd_menu();
			} else {
				open_ingelogd_menu();
			}
		}
	});

	// Toggle lateral menu clicking on the trigger
	$lateral_menu_toggle.on('click', function (event) {
		event.preventDefault();

		if ($lateral_menu_toggle.hasClass('is-clicked')) {
			close_lateral_menu();
		} else {
			open_lateral_menu();
		}
	});

	// Close all menus clicking outside the menu itself
	$content_wrapper.on('click', function (event) {
		if (!$(event.target).is('#cd-ingelogd-menu-toggle, #cd-ingelogd-menu-toggle span, #cd-lateral-menu-toggle, #cd-lateral-menu-toggle span')) {
			// Close menus
			close_ingelogd_menu();
			close_lateral_menu();
		}
	});

	// Catch keystrokes for instant search
	$(document).keydown(function (event) {

		// Geen instantsearch met modifiers
		if (bCtrlPressed || bAltPressed || bMetaPressed) {
			return;
		}

		// Geen instantsearch als we in een input-element of text-area zitten.
		var element = event.target.tagName.toUpperCase();
		if (element == 'INPUT' || element == 'TEXTAREA' || element == 'SELECT') {
			return;
		}

		// a-z en 0-9 incl. numpad
		if ((event.keyCode > 64 && event.keyCode < 91) || (event.keyCode > 47 && event.keyCode < 58) || (event.keyCode > 95 && event.keyCode < 106)) {
			$('#cd-zoek-form').find('.menuzoekveld').focus();
			open_lateral_menu();
		}
	});

	$('#cd-zoek-form').find('.menuzoekveld').keyup(function (event) {
		if (event.keyCode === 27) { // esc
			close_lateral_menu();
		}
	}).on('focus', function (event) {
		this.setSelectionRange(0, this.value.length);
	});
});
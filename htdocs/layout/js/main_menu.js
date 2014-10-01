jQuery(document).ready(function ($) {

	var $ingelogd_menu_trigger = $('#cd-ingelogd-menu-trigger');
	var $ingelogd_menu = $('#cd-ingelogd-menu');
	var $lateral_menu_trigger = $('#cd-lateral-menu-trigger');
	var $content_wrapper = $('.cd-main-content');
	var $header = $('header');

	//toggle ingelogd menu clicking on the name item
	$ingelogd_menu_trigger.on('click', function (event) {
		if (!$(event.target).is('#cd-ingelogd-menu a')) {
			event.preventDefault();

			$ingelogd_menu_trigger.toggleClass('ingelogd-menu-is-open');
			$ingelogd_menu.slideToggle();
		}
	});

	//toggle lateral menu clicking on the menu icon
	$lateral_menu_trigger.on('click', function (event) {
		event.preventDefault();

		$lateral_menu_trigger.toggleClass('is-clicked');
		$header.toggleClass('lateral-menu-is-open');
		$content_wrapper.toggleClass('lateral-menu-is-open');
		$('#cd-lateral-nav').toggleClass('lateral-menu-is-open');
	});

	//close all menus clicking outside the menu itself
	$content_wrapper.on('click', function (event) {
		if (!$(event.target).is('#cd-ingelogd-menu-trigger, #cd-ingelogd-menu-trigger span, #cd-lateral-menu-trigger, #cd-lateral-menu-trigger span')) {
			//close ingelogd menu
			$ingelogd_menu_trigger.removeClass('ingelogd-menu-is-open');
			$ingelogd_menu.slideUp();

			//close lateral menu
			$lateral_menu_trigger.removeClass('is-clicked');
			$header.removeClass('lateral-menu-is-open');
			$content_wrapper.removeClass('lateral-menu-is-open');
			$('#cd-lateral-nav').removeClass('lateral-menu-is-open');
		}
	});

	//open (or close) submenu items in the lateral menu. Close all the other open submenu items.
	$('.item-has-children').children('a').on('click', function (event) {
		event.preventDefault();

		$(this).toggleClass('sub-menu-open').next('.sub-menu').slideToggle(200).end().parent('.item-has-children').siblings('.item-has-children').children('a').removeClass('sub-menu-open').next('.sub-menu').slideUp(200);
	});

	//open lateral menu for instant search
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
		if (event.keyCode > 64 && event.keyCode < 91) {
			$('#menuZoekveld').focus();

			//open lateral menu
			$lateral_menu_trigger.addClass('is-clicked');
			$header.addClass('lateral-menu-is-open');
			$content_wrapper.addClass('lateral-menu-is-open');
			$('#cd-lateral-nav').addClass('lateral-menu-is-open');
		}
	});
});
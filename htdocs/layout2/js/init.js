// On Document.ready
$(function () {


	// Random rotation between -2 and +2 degrees
	$('.rotate').each(function () {

		// Random int between -2 and +2
		var random = Math.random() * 4 - 2;

		// Apply rotation css to element
		// Note: jQuery auto-adds vendor-prefixes (eg -moz-) if necessary
		$(this).css('transform', 'rotate(' + random + 'deg)');

	});


	// Background image
	$.backstretch('http://plaetjes.csrdelft.nl/layout2/bg-image-16.jpg');


	// Login form enhancing
	$('.login-form input.text').focus(function () {
		$(this).parents('.flip').addClass('flipped');
	}).blur(function () {
		$(this).parents('.flip').removeClass('flipped');
	});

	if (is_touch_device()) {
		$('.flip').click(function () {
			$(this).addClass("flipped");
		});
	}


	// TODO: cross-fade images at homepage


	// Modal popup closing
	if ($("#blackout").size())
		$("body").css({overflowY: "hidden"});

	$('body').bind('click.outside', function (e) {
		if (!$(e.target).parents('#pageover').length > 0 && !$(e.target).hasClass('pp_overlay') && !$(e.target).hasClass('#dragobject')) {
			escape(e);
		}
	});
	$(document).keyup(function (e) {
		if (e.keyCode == 27) { // esc
			escape(e);
		}
	});
	$('.close').click(escape);

	// take first image and put it in the clip
	/* var imageHolder = $("div.pg-mid img.REPLACE-ANCHOR");
	 var image = $("div.pg-mid div.content img.ubb_img").first();
	 imageHolder.attr("src", image.attr("src"));
	 image.hide(); */

	$("div.pg-mid img.rotate-left, div.pg-mid img.rotate-right").each(function () {

		$(this).css({margin: 0}).wrap('<figure class="' + $(this).attr("class") + '" />').after('<div class="clip"></div>');

	});


	// Random rotation
	$('figure.rotate-left, figure.rotate-right').each(function () {
		var random = Math.random() * 4 - 2; // Random between -2 and +2 degrees
		$(this).css('-webkit-transform', 'rotate(' + random + 'deg)');
		$(this).css('-moz-transform', 'rotate(' + random + 'deg)');
	});

	// Filmpjes meuk
	$("#video_unit").each(function () {

		el = $(".videos > li");
		el.find("> a").click(function () {

			el.removeClass("active");
			$(this).parent().addClass("active");

			var index = $(this).parent().parent().find("> li").index($(this).parent());


			$("#video_unit .video iframe").attr("src", video_unit_videos[index]);

			return false;

		});

	});

});

function escape(e) {
	$('#blackout').css({overflowY: "hidden"});
	$('body').css({overflowY: "scroll"});
	$('#pageover').animate({top: '100%'}, 300);
	$('#blackout').fadeOut(300, function () {
		$(this).remove();
	});
	return false;
}

function is_touch_device() {
	return !!('ontouchstart' in window) // works on most browsers
			|| !!('onmsgesturechange' in window); // works on ie10
}
;
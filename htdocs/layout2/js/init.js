// On Document.ready
$(function(){


	// Random rotation between -2 and +2 degrees
	$('.rotate').each(function(){

		// Random int between -2 and +2
		var random = Math.random() * 4 - 2;

		// Apply rotation css to element
		// Note: jQuery auto-adds vendor-prefixes (eg -moz-) if necessary
		$(this).css('transform', 'rotate(' + random + 'deg)');

	});


	// Background image
	$.backstretch('/images/layout2/bg-image-16.jpg');


	// Login form enhancing
	$('.login-form input.text').focus(function(){
		$(this).parents('.flip').addClass('flipped');
	}).blur(function(){
		$(this).parents('.flip').removeClass('flipped');
	});
	// Submit form by clicking link
	$('.login-submit').click(function(e){
		$('.login-form form').submit();
		e.preventDefault();
	});


	if (is_touch_device()) {
		$('.flip').click(function(){
			$(this).addClass("flipped");
		});
	}


	// TODO: cross-fade images at homepage


	// Modal popup closing
	// TODO: close when clicking outside of the pageover
	$(document).on('click', '.close', function(){
		$(this).parents('#pageover').animate({ top: '100%' }, 300);
		$(this).parents('#blackout').fadeOut(300, function(){
			$(this).remove();
		});
		return false;
	});

	// take first image and put it in the clip
	var imageHolder = $("div.mid img.REPLACE-ANCHOR");
	var image = $("div.mid div.content img.ubb_image").first();
	imageHolder.attr("src", image.attr("src"));
	image.hide();
});


function is_touch_device() {
  return !!('ontouchstart' in window) // works on most browsers
      || !!('onmsgesturechange' in window); // works on ie10
};
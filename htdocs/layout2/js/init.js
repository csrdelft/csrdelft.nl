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
	$.backstretch('/images/layout2/bg-image-14.jpg');


	// Login form enhancing
	$('.login-form input.text').each(function(){
		$(this).data('initial-text', $(this).val());
	}).focus(function(){
		$(this).parents('.flip').addClass('flipped');
		if ($(this).val() == $(this).data('initial-text'))
			$(this).val('');
	}).blur(function(){
		$(this).parents('.flip').removeClass('flipped');
		if ($(this).val() == '')
			$(this).val($(this).data('initial-text'));
	});
	$('.login-submit').click(function(e){
		$('.login-form form').submit();
		e.preventDefault();
	});


	// TODO: cross-fade images at homepage


	// Modal popup closing
	// TODO: close when clicking outside of the pageover
	$(document).on('click', '.close', function(){
		$(this).parents('#pageover').animate({ top: '100%' }, 300);
		$(this).parents('#blackout').fadeOut(300, function(){
			$(this).remove();
		});
	});



});
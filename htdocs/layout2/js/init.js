$(function(){


	// Random rotation
	$('.rotate').each(function(){
		var random = Math.random() * 4 - 2; // Random between -2 and +2 degrees
		$(this).css('-webkit-transform', 'rotate(' + random + 'deg)');
		$(this).css('-moz-transform', 'rotate(' + random + 'deg)');
	});


	// Background image
	$.backstretch('/images/layout2/bg-image-14.jpg');


	// TODO: cross-fade images at homepage


	// Modal popup closing
	// TODO: close when clicking outside of the pageover
	$('.close').click(function(){
		$(this).parents('#pageover').animate({ top: '100%' }, 300);
		$(this).parents('#blackout').fadeOut(300, function(){
			$(this).remove();
		});
	});


	


});
$(document).ready(function() {
	$('.dies-activiteit-ketzers').each(function() {
		const toggler = $(this).find('.toggler');
		const content = $(this).find('.ketzers');
		toggler.click(function() {
			content.toggleClass('verborgen');
		});
	});
});

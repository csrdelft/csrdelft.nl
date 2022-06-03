$(document).ready(function() {
	$('.dies-activiteit-ketzers').each(function() {
		const text1 = "Laat ketzers zien"
		const text2 = "Verberg ketzers"
		const toggler = $(this).find('.toggler');
		const content = $(this).find('.ketzers');
		toggler.click(function() {
			toggler.text(toggler.text() == text2 ? text1 : text2);
			content.toggleClass('verborgen');
		});
	});
});

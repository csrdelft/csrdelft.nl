/**
 * ubb_url.js	|	P.W.G. Brussee (brussee@live.nl)
 * 
 * requires jQuery
 */

$(document).ready(function() {
	$('a.verlaatstek').each(function() {
		$(this).click(ubb_url_verlaatstek);
	});
});

function ubb_url_verlaatstek(event) {
	if (!confirm('U gaat naar:\n\n' + $(this).attr('href') + '\n\nU verlaat de stek!')) {
		event.preventDefault();
		return false;
	}
	return true;
}
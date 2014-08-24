/*!
 * csrdelft.js
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De stek layout uit 2014
 */

var shiftPressed = false;
var ctrlPressed = false;

$(document).ready(function() {
	init_keyPressed();
	init_timeago();
});

function init_keyPressed() {
	$(window).keydown(function(event) {
		if (event.which === 16) { // shift
			shiftPressed = true;
		}
		else if (event.which === 17) { // ctrl
			ctrlPressed = true;
		}
	});
	$(window).keyup(function(event) {
		if (event.which === 16) { // shift
			shiftPressed = false;
		}
		else if (event.which === 17) { // ctrl
			ctrlPressed = false;
		}
	});
}

function init_timeago() {
	$.timeago.settings.strings = {
		prefiprefixAgo: "",
		prefixFromNow: "sinds",
		suffixAgo: "geleden",
		suffixFromNow: "",
		seconds: "minder dan een minuut",
		minute: "1 minuut",
		minutes: "%d minuten",
		hour: "1 uur",
		hours: "%d uur",
		day: "een dag",
		days: "%d dagen",
		month: "een maand",
		months: "%d maanden",
		year: "een jaar",
		years: "%d jaar",
		wordSeparator: " ",
		numbers: []
	};
	$('abbr.timeago').timeago();
}

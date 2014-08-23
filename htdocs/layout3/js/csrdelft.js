/*!
 * csrdelft.js
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De stek layout uit 2014
 */

$(document).ready(function() {
	init_timeago();
});

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

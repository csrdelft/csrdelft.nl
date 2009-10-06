/*
 *	Documentenketzerjavascriptcode.
 */

$(document).ready(function() {

	$("input[name='methode']").change(
		function(){
			methodenaam=$("input[name='methode']:checked").val();
			id="#Methode"+methodenaam.charAt(0).toUpperCase()+methodenaam.substr(1).toLowerCase();

			$(".keuze").fadeOut(100);
			$(id).fadeIn(100);
		});
});

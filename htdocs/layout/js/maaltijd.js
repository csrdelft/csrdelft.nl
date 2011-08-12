/* 
 * maaltijdketzer
 */

jQuery(document).ready(function(){
	//reset knopje maaltijdtoevoegformulier
	corveeVeldResetter();

	//scrolbare tabel maken op corveebeheerpagina
	$('#corveebeheer').tableScroll({height:320});

})

//zet inputs voor corveetaken op nul
function corveeVeldResetter(){
	jQuery(".knop.zetopnul").click(function (event) {
		event.preventDefault();
		jQuery("#corveevelden").children("div").children("input[type=text]").val(0);
	});
} 

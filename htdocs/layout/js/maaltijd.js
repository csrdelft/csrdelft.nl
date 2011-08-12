// maaltijdketzer
jQuery(document).ready(function(){
	corveeVeldResetter();
})

function corveeVeldResetter(){
	jQuery(".knop.zetopnul").click(function (event) {
		event.preventDefault();
		jQuery("#corveevelden").children("div").children("input[type=text]").val(0);
	});
} 

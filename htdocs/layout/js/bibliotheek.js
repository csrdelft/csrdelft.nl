/*
 *	Bibliotheekjavascriptcode.
 */
jQuery(document).ready(function($) {
	//tabellen naar zebra converteren.
	$("#boeken tr:odd").addClass('odd');
	
	//hippe sorteerbare tabel fixen.
	$("#boekencatalogus").dataTable({
		"oLanguage": {
			"sZeroRecords": "Geen boeken gevonden",
			"sInfoEmtpy": "Geen boeken gevonden",
			"sSearch": "Zoeken:",
			oPaginate:{
				"sFirst": "Eerste",
				"sPrevious": "Vorige",
				"sNext": "Volgende",
				"sLast": "Laatste"}
		},
		"iDisplayLength": 20,
		"bInfo": false,
		"bLengthChange": false,
		"aaSorting": [[1, 'asc']],
		"sPaginationType": "full_numbers",
		"aoColumns": [
			{'sType': 'html'}, // documentnaam
			{'sType': 'html'}, // auteur
			{'sType': 'html'}, // rubriek
			{'sType': 'html'}, // code
			{'sType': 'html'} // ISBN
		],
		"aoColumnDefs": [ 
			{ "bVisible": false, "aTargets": [ 3,  4] }
		]
	});
	
	// velden bewerkbaar maken
	observeClick();
	biebCodeVakvuller();
});

function observeClick(){
	jQuery(".bewerk").click(function(){
		var ID=jQuery(this).attr('id');
		jQuery("#waarde_"+ID).hide();
		jQuery("#waarde_input_"+ID).show();
	}).change(function(){
		var ID=jQuery(this).attr('id');
		var boekid=jQuery(".boek").attr('id');
		var waarde=jQuery("#waarde_input_"+ID).val();
		var dataString = 'id='+ID+'&waarde='+ waarde;
		jQuery("#waarde_"+ID).html('Laad...'); // Loading

		jQuery.ajax({
			type: "POST",
			url: '/communicatie/bewerkboek/'+ boekid,
			data: dataString,
			cache: false,
			success: function(result){
				jQuery("#waarde_"+ID).html(result);
			}
		});
	});

	// Outside click action
	jQuery(document).mouseup(function(object){
		if(!jQuery(object.target).hasClass("editbox")){ //in editbox mag je klikken
			jQuery(".editbox").hide();
			jQuery(".text").show();
		}
	});

};

function biebCodeVakvuller(){
	$(".knop.genereer").click(function (event) {
		event.preventDefault();
		jQuery("#field_code").val(jQuery("#field_rubriek").val() + '.' + jQuery("#field_auteur").val().substring(0,3).toLowerCase());
	});
}  

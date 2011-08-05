/*
 *	Bibliotheekjavascriptcode.
 */
jQuery(document).ready(function($) {
	//tabellen naar zebra converteren.
	jQuery("#boeken tr:odd").addClass('odd');
	
	//hippe sorteerbare tabel fixen.
	jQuery("#boekencatalogus").dataTable({
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
		"iDisplayLength": 30,
		"bInfo": false,
		"bLengthChange": false,
		"aaSorting": [[0, 'asc']],
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
	
	//hippe sorteerbare tabel fixen.
	jQuery("#boekenbeheerlijsten").dataTable({
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
		"iDisplayLength": 30,
		"bInfo": false,
		"bLengthChange": false,
		"aaSorting": [[0, 'asc']],
		"sPaginationType": "full_numbers",
		"aoColumns": [
			{'sType': 'html'}, // titel
			{'sType': 'html'}, // titel
			{'sType': 'html'}, // auteur
			{'sType': 'html'}, // rubriek
			{'sType': 'html'}, // code
			{'sType': 'html'}, // ISBN
			{'sType': 'html'}, // Aantal beschrijvingen
			{'sType': 'html'}, // eigenaars
			{'sType': 'html'}, // lener
			{'sType': 'html'} // status
		],
		"aoColumnDefs": [ 
			{ "bVisible": false, "aTargets": [ 1, 2, 3, 5] }
		]
	});

	// velden bewerkbaar maken
	observeClick();
	biebCodeVakvuller();
});

function observeClick(){
	jQuery(".bewerk").click(function(){
		var ID=jQuery(this).attr('id');
		jQuery("#"+ID+" span.text").hide();
		jQuery("#"+ID+" .editbox").show();
		jQuery("#"+ID+" .editelements").show();
	}).change(function(){
		var ID=jQuery(this).attr('id');
		var boekid=jQuery(".boek").attr('id');
		var waarde=jQuery("#"+ID+" input,#"+ID+" select,#"+ID+" textarea").val();
		var dataString = 'id='+ID+'&'+ID+'='+ waarde;

		jQuery.ajax({
			type: "POST",
			url: '/communicatie/bibliotheek/bewerkboek/'+ boekid,
			data: dataString,
			cache: false,
			success: function(result){
				jQuery("#"+ID+" span.text").html(result);
			}
		});
	});


	// Outside click action
	jQuery(document).mouseup(function(object){
		if(!(jQuery(object.target).hasClass("editbox") || jQuery(object.target).attr('id').substring(0,6)=='tat_td')){ //in editbox mag je klikken
			jQuery(".editbox").hide();
			jQuery('#[name^="tat_td"]').hide();
			jQuery(".text").show();
			jQuery(".editelements").hide();
		}
	});

};



function biebCodeVakvuller(){
	jQuery(".knop.genereer").click(function (event) {
		event.preventDefault();
		jQuery("#field_code").val(jQuery("#field_rubriek").val() + '.' + jQuery("#field_auteur").val().substring(0,3).toLowerCase());
	});
}  
function naamCheck(fieldname){
	field=document.getElementById('field_'+fieldname);
	if(field.value.length>2){
		http.abort();
		http.open("GET", "/tools/naamlink.php?naam="+field.value, true);
		http.onreadystatechange=function(){
			if(http.readyState == 4){
				document.getElementById('preview_'+fieldname).innerHTML=http.responseText;
			}
		}
		http.send(null);
	}else{
		document.getElementById('preview_'+fieldname).innerHTML='';
	}
	return null;
}

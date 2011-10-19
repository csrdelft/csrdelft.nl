/*
 *	Bibliotheekjavascriptcode.
 */
jQuery(document).ready(function($) {
	//tabellen naar zebra converteren.
	jQuery("#boeken tr:odd").addClass('odd');

	//hippe sorteerbare tabel fixen.
	var oTableCatalogus = jQuery("#boekencatalogus").dataTable({
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
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "/communicatie/bibliotheek/catalogusdata",
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "sFilter", "value": $('input:radio[name=filter-catalogus]:checked').val() } );
		},
		"iDisplayLength": 30,
		"bInfo": false,
		"bLengthChange": false,
		"aaSorting": [[0, 'asc']],
		"sPaginationType": "full_numbers",
		"aoColumns": [
			{'sType': 'html',"sWidth": "400px"}, // titel
			{'sType': 'html'}, // auteur
			{'sType': 'html',"sWidth": "300px"}, // rubriek

		]
	});

	//update de tabel als de radiobuttons worden gebruikt
	$('input:radio[name=filter-catalogus]').click( function() { oTableCatalogus.fnDraw(); } );


	//hippe sorteerbare tabel fixen.
	var oTableBoekstatus = jQuery("#boekenbeheerlijsten").dataTable({
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
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "/communicatie/bibliotheek/boekstatusdata",
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "sFilter", "value": $('input:radio[name=filter-boekstatus]:checked').val() } );
		},
		"iDisplayLength": 30,
		"bInfo": false,
		"bLengthChange": false,
		"aaSorting": [[0, 'asc']],
		"sPaginationType": "full_numbers",
		"aoColumns": [
			{'sType': 'html',"sWidth": "250px"}, // titel
			{'sType': 'html'}, // code
			{'sType': 'html'}, // Aantal beschrijvingen
			{'sType': 'html',"sWidth": "150px"}, // eigenaars
			{'sType': 'html',"sWidth": "150px"}, // lener
			{'sType': 'html'}, // status
			{'sType': 'html'} //aantal leningen
		]
	});

	//update de tabel als de radiobuttons worden gebruikt
	$('input:radio[name=filter-boekstatus]').click( function() { oTableBoekstatus.fnDraw(); } );

	// velden bewerkbaar maken
	observeClick();
	biebCodeVakvuller();
});

function observeClick(){
	jQuery(".bewerk").click(function(){
		//show edit field
		jQuery(this).children('span.text').hide();
		jQuery(this).children('.editbox,.editelement').show();
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
		if(!(jQuery(object.target).hasClass("editbox") 						//in editbox mag je klikken
			|| jQuery(object.target).attr('id').substring(0,6)=='tat_td'	//entry van suggestiemenu
			|| jQuery(object.target).text()=='\\/' 							//movedownarraw suggestiemenu
			|| jQuery(object.target).text()=='/\\' )){ 						//moveuparrow suggestiemenu 
				jQuery(".editbox,.editelement").hide();
				jQuery('[name^="#tat_td"]').hide();
				jQuery(".text").show();
		}
	});

};



function biebCodeVakvuller(){
	jQuery(".knop.genereer").click(function (event) {
		event.preventDefault();
		jQuery("#field_code").val(
			jQuery("#field_rubriek").val() + '.' + jQuery("#field_auteur").val().substring(0,3).toLowerCase()
		);
		jQuery("#field_code").trigger('change');
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

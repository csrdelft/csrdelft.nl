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
		//"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "/communicatie/bibliotheek/catalogusdata",
		"fnServerParams": function ( aoData ) {
			aoData.push( { "name": "sEigenaarFilter", "value": $('input:radio[name=filter-catalogus]:checked').val() } );
			aoData.push( { "name": "sView", "value": $('input[name=boekstatus]').is(':checked') } );
		},
		"iDisplayLength": 30,
		"bInfo": false,
		"bLengthChange": false,
		"bStateSave": true,
		"iCookieDuration": 60*15, // 15 min
		"fnStateSaveCallback": function ( oSettings, sValue ) {
			sValue += ',"sEigenaarFilter": "'+$('input:radio[name=filter-catalogus]:checked').val()+'"';
			sValue += ',"sView": '+$('input[name=boekstatus]').is(':checked');
			return sValue;
		},
		"fnStateLoadCallback": function ( oSettings, oData ) {
			$('input:radio[name=filter-catalogus]').val([oData.sEigenaarFilter]);
			$('input[name=boekstatus]').attr('checked', oData.sView);
			return true;
		},
		"aaSorting": [[0, 'asc']],
		"sPaginationType": "full_numbers",
		"aoColumns": getKolommen()
	});

	function getKolommen() {
		var kolommen;
		if($("#boekencatalogus").hasClass("lid")){
			kolommen = {"aoKolommen": [
				{'sType': 'html'}, // titel
				{'sType': 'html'}, // auteur
				{'sType': 'html'}, // rubriek
				{'sType': 'html',"sWidth": "40px"},
				{'sType': 'html',"sWidth": "125px", "bVisible": false}, // eigenaar
				{'sType': 'html',"sWidth": "125px", "bVisible": false}, //uitgeleend aan
				{'sType': 'html',"sWidth": "100px", "bVisible": false} // uitleendatum
			]};
		}else{
			kolommen = {"aoKolommen": [
				{'sType': 'html',"sWidth": "400px"}, // titel
				{'sType': 'html'}, // auteur
				{'sType': 'html',"sWidth": "300px"} // rubriek
			]};
		}
		return kolommen.aoKolommen;
	}

	//update de tabel als de radiobuttons of checkbox worden gebruikt
	$('input:radio[name=filter-catalogus]').click( function() { oTableCatalogus.fnDraw(); } );
	$('input#boekstatus').click( function() { 
		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var oTable = $('#boekencatalogus').dataTable();
	
		var bVis = $('input[name=boekstatus]').is(':checked');
		oTable.fnSetColumnVis( 6, bVis, false);
		oTable.fnSetColumnVis( 5, bVis, false);
		oTable.fnSetColumnVis( 4, bVis, true );
	 } );

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
			|| object.target.id.substring(0,6)=='tat_td'	//entry van suggestiemenu
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

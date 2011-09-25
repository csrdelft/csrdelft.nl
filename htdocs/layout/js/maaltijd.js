/* 
 * maaltijdketzer
 */

jQuery(document).ready(function(){
	//reset knopje maaltijdtoevoegformulier
	corveeVeldResetter();

	//wijzigingen van checkboxes op abobeheerpagina verwerken
	observeCheckboxesAbos();

	//tabellen naar zebra converteren.
	jQuery("#abolijst tr:odd").addClass('odd');

	//hippe sorteerbare tabel fixen.
	var oTable = jQuery("#abolijst").dataTable({
		"oLanguage": {
			"sZeroRecords": "Geen leden gevonden",
			"sInfoEmtpy": "Geen leden gevonden",
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
		"aaSorting": [[1, 'asc']],
		"sPaginationType": "full_numbers",
		"aoColumns": [
			{'iDataSort': 7},	// naam
			{'sType': 'html'}, 				// waarschuwing
			{'sType': 'html'},	// jaar
			{'sSortDataType': 'dom-checkbox' }, // maandagabo
			{'sSortDataType': 'dom-checkbox' }, // donderdagabo
			{'sType': 'html'},	// verticale
			{'sSortDataType': 'dom-checkbox' }, // verticaleabo
			{'sType': 'html'}	// achternaam(verborgen)
		],
		"aoColumnDefs": [ 
			{ "bVisible": false, "aTargets": [ 7] }
		]
	});
	/* Add event listeners to the two range filtering inputs */
	$('#filterwaarschuwingen').click( function() { oTable.fnDraw(); } );
})
/* Create an array with the values of all the checkboxes in a column */
$.fn.dataTableExt.afnSortData['dom-checkbox'] = function  ( oSettings, iColumn )
{
	var aData = [];
	$( 'td:eq('+iColumn+') input', oSettings.oApi._fnGetTrNodes(oSettings) ).each( function () {
		aData.push( this.checked==true ? "1" : "0" );
	} );
	return aData;
}
$.fn.dataTableExt.afnFiltering.push(
	function( oSettings, aData, iDataIndex ) {
		if(jQuery('#filterwaarschuwingen').is(':checked')){
			if(aData[1].length >6){ //de standaardwaarde &nbsp; is 6 tekens
				return true;
			}else{
				return false;
			}
		}else{
			return true;
		}
	}
);
function observeCheckboxesAbos(){
	jQuery(".abovinkje").change(function(){
		var ID =jQuery(this).attr('id');
		var ids=ID.split('-');
		var uid=ids[0];
		var abo=ids[1];
		var actie;
		jQuery(this).children('input').each(function(index){
			if(jQuery(this).is(':checked')){
				actie = 'add';
			}else{
				actie = 'delete';
			}
		});
		var data = 'uid='+uid+'&'+'abo='+abo;

		jQuery.ajax({
			type: "POST",
			url: '/actueel/maaltijden/abonnementenbeheer/abo/'+actie,
			data: data,
			cache: false,
			success: function(result){
				if(result=='Abonnementwijziging gelukt'){
					jQuery("#"+ID).css({'background-color': 'green !important'}).attr('title', result);
				}else{
					jQuery("#"+ID).css({'background-color': 'red !important'}).attr('title', result);
				}
			}
		});
	});
}
//zet inputs voor corveetaken op nul
function corveeVeldResetter(){
	jQuery(".knop.zetopnul").click(function (event) {
		event.preventDefault();
		jQuery("#corveevelden").children("div").children("input[type=text]").val(0);
	});
} 

//regelt de ajaxrequest als actieknoppen van corveeresetter worden gebruikt
function corveeResetter(actie){
	//data verzamelen
	var data="resetactie="+actie;
	jQuery('#resetForm').children("fieldset").children("select").each(function(index){
		data=data+"&"+jQuery(this).attr("name")+"="+jQuery(this).val();
	});
	//datumveld disablen
	jQuery('#resetForm').children("fieldset").children("select").attr('disabled', 'disabled');
	//actie uitvoeren
	jQuery.ajax({
		type: "POST",
		url: '/actueel/maaltijden/corveeinstellingen/',
		data: data,
		cache: false,
		success: function(response){
			//controleknop terugzetten
			jQuery('#controleContainer').hide();
			jQuery('#resetContainer').html(response);
			jQuery('#resetContainer').show();
			jQuery("#resetcontrolletabel tr:odd").addClass('odd');
		}
	});
}
//zet corveeresetter terug naar datuminvulveld
function restoreCorveeResetter(){
	//enable datumveld
	jQuery('#resetForm').children("fieldset").children("select").removeAttr('disabled');
	//controleknop terugzetten
	jQuery('#controleContainer').show();
	jQuery('#resetContainer').hide();
}

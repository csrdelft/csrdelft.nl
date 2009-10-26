/*
 *	Documentenketzerjavascriptcode.
 */
function updateForm(){
	var methodenaam=$("input[name='methode']:checked").val();
	id="#"+methodenaam;
	$(".keuze").fadeOut(100);
	$(id).fadeIn(100);
}
$(document).ready(function() {
	if($("#documentForm").length > 0){
		updateForm();
		//bij het wijzigen van een input doen we weer een update op het formulier.
		$("input[name='methode']").change(updateForm);
	}
	//tabellen naar zebra converteren.
	$("#documenten tr:odd").addClass('odd');
	
	//hippe sorteerbare tabel fixen.
	$("#documentencategorie").dataTable({
		"oLanguage": {
			"sZeroRecords": "Geen documenten gevonden",
			"sInfoEmtpy": "Geen documenten gevonden",
			"sSearch": "Zoeken:"
		},
		"iDisplayLength": 50,
		"bInfo": false,
		"bLengthChange": false,
		"aoColumns": [
			null, // documentnaam
			//Bestandstgrootte naar B/KB omzetten.
			{"fnRender":
				function(oObj){
					return readableFileSize(oObj.aData[1]);
				},
				"bUseRendered": false
			}, 
			null, //mime-type
			null, //toegevoegd
			null //Eigenaar
			]
		});

});

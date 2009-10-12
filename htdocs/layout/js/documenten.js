/*
 *	Documentenketzerjavascriptcode.
 */

$(document).ready(function() {
	//formulierding
	$("input[name='methode']").change(
		function(){
			methodenaam=$("input[name='methode']:checked").val();
			id="#Methode"+methodenaam.charAt(0).toUpperCase()+methodenaam.substr(1).toLowerCase();

			$(".keuze").fadeOut(100);
			$(id).fadeIn(100);
		});
 	//tabellen naar zebra converteren.
	$("#documenten tr:odd").addClass('odd');

	//hippe sorteerbare tabel fixen.
	$("#documentencategorie").dataTable({
		"oLanguage": {
			"sLengthMenu": "Toon _MENU_ documenten per pagina",
			"sZeroRecords": "Geen documenten gevonden",
			"sInfo": "Toon _START_ tot _END_ van _TOTAL_ documenten",
			"sInfoEmtpy": "Geen documenten gevonden",
			"sSearch": "Zoeken:",
			"sInfoFiltered": "(Gefilterd uit _MAX_ documenten)"
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

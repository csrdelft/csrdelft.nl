/**
 *	Documentenketzerjavascriptcode.
 */
jQuery(document).ready(function($) {

	jQuery('.BestandUploaderOptie').change(function() {
		var optie = jQuery('input.BestandUploaderOptie:checked').parent();
		optie = jQuery('div.UploadKeuze', optie);
		jQuery('div.UploadKeuze').not(optie).fadeOut(250);
		optie.fadeIn(250);
	});

	//tabellen naar zebra converteren.
	$("#documenten tr:odd").addClass('odd');

	//hippe sorteerbare tabel fixen.
	$("#documentencategorie").dataTable({
		"oLanguage": {
			"sZeroRecords": "Geen documenten gevonden",
			"sInfoEmtpy": "Geen documenten gevonden",
			"sSearch": "Zoeken:"
		},
		"iDisplayLength": 20,
		"bInfo": false,
		"bLengthChange": false,
		"aaSorting": [[3, 'desc']],
		"aoColumns": [
			{'sType': 'html'}, // documentnaam
			//Bestandstgrootte naar B/KB omzetten.
			{"fnRender":
						function(oObj) {
							return readableFileSize(oObj.aData[1]);
						},
				"bUseRendered": false
			},
			null, //mime-type
			{'sType': 'html'}, //moment toegevoegd
			null //Eigenaar
		]
	});
});

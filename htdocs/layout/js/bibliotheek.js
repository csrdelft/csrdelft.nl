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
});

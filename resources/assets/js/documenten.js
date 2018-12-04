import $ from 'jquery';

/**
 * Documentenketzerjavascriptcode.
 */
$(() => {
	//tabellen naar zebra converteren.
	$('#documenten').find('tr:odd').addClass('odd');

	//hippe sorteerbare tabel fixen.
	$('#documentencategorie').dataTable({
		language: {
			zeroRecords: 'Geen documenten gevonden',
			infoEmtpy: 'Geen documenten gevonden',
			search: 'Zoeken:',
		},
		displayLength: 20,
		info: false,
		lengthChange: false,
		sorting: [[3, 'desc']], // moment toegevoegd
		columns: [
			{type: 'html'}, // documentnaam
			null, // Bestandsgrootte
			{type: 'string'}, // mime-type, forceer string anders werkt sorteren uberhaupt niet
			{render: $.fn.dataTable.render.timeago}, //moment toegevoegd
			null, // Eigenaar
		],
	});
});

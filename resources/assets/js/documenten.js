import $ from 'jquery';

/**
 * Documentenketzerjavascriptcode.
 */
$(() => {
	let $documenten = $('#documenten');

	//tabellen naar zebra converteren.
	$documenten.find('tr:odd').addClass('odd');
	// render de filesize cellen
	$documenten.find('.size').each((i, el) => el.innerText = $.fn.dataTable.render.filesize(el.innerText, 'display'));

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
			{type: 'num', render: $.fn.dataTable.render.filesize}, // Bestandsgrootte
			{type: 'string'}, // mime-type, forceer string anders werkt sorteren uberhaupt niet
			{render: $.fn.dataTable.render.timeago}, //moment toegevoegd
			null, // Eigenaar
		],
	});
});

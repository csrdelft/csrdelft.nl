import $ from 'jquery';
import render from './datatable/render';

/**
 * Documentenketzerjavascriptcode.
 */
$(() => {
	let $documenten = $('#documenten');

	//tabellen naar zebra converteren.
	$documenten.find('tr:odd').addClass('odd');
	// render de filesize cellen
	$documenten.find('.size').each((i, el) => el.innerText = render.filesize(el.innerText, 'display'));

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
			{type: 'num', render: render.filesize}, // Bestandsgrootte
			{type: 'string'}, // mime-type, forceer string anders werkt sorteren uberhaupt niet
			{render: render.timeago}, //moment toegevoegd
			null, // Eigenaar
		],
	});
});

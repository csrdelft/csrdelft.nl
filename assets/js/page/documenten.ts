import $ from 'jquery';
import render from '../datatable/render';
import CellMetaSettings = DataTables.CellMetaSettings;

/**
 * Documentenketzerjavascriptcode.
 */
$(async () => {
	await import('../datatable/bootstrap');

	const $documenten = $('#documenten');

	// tabellen naar zebra converteren.
	$documenten.find('tr:odd').addClass('odd');
	// render de filesize cellen
	$documenten
		.find('.size')
		.each((i, el) => (el.innerText = render.filesize(el.innerText, 'display', null, {} as CellMetaSettings)));

	$('#documentencategorie').DataTable({
		columns: [
			{ type: 'html' }, // documentnaam
			{ type: 'num', render: render.filesize }, // Bestandsgrootte
			{ type: 'string' }, // mime-type, forceer string anders werkt sorteren uberhaupt niet
			{ render: render.timeago }, // moment toegevoegd
			{}, // Eigenaar
		],
		info: false,
		language: {
			infoEmpty: 'Geen documenten gevonden',
			search: 'Zoeken:',
			zeroRecords: 'Geen documenten gevonden',
		},
		lengthChange: false,
		order: [[3, 'desc']], // moment toegevoegd
		pageLength: 20,
	});
});

import $ from 'jquery';
import initContext from '../context';

export default {
	deferRender: true,
	createdRow(tr, data) {
		let table = this;
		$(tr).attr('data-uuid', data.UUID);
		initContext(tr);

		$(tr).children().each((columnIndex, td) => {
			// Init custom buttons in rows
			$(td).children('a.post').each((i, a) => {
				$(a).attr('data-tableid', table.attr('id'));
			});
		});
	},
	lengthMenu: [
		[10, 25, 50, 100, -1],
		[10, 25, 50, 100, 'Alles']
	],
	language: {
		sProcessing: 'Bezig...',
		sLengthMenu: '_MENU_ resultaten weergeven',
		sZeroRecords: 'Geen resultaten gevonden',
		sInfo: '_START_ tot _END_ van _TOTAL_ resultaten',
		sInfoEmpty: 'Geen resultaten om weer te geven',
		sInfoFiltered: ' (gefilterd uit _MAX_ resultaten)',
		sInfoPostFix: '',
		sSearch: 'Zoeken',
		sEmptyTable: 'Geen resultaten aanwezig in de tabel',
		sInfoThousands: '.',
		sLoadingRecords: 'Een moment geduld aub - bezig met laden...',
		oPaginate: {
			sFirst: 'Eerste',
			sLast: 'Laatste',
			sNext: 'Volgende',
			sPrevious: 'Vorige'
		},
		select: {
			rows: {
				'_': '%d rijen geselecteerd',
				'0': '',
				'1': '1 rij geselecteerd'
			}
		},
		buttons: {
			copy: 'Kopiëren',
			print: 'Printen',
			colvis: 'Kolom weergave'
		},
		// Eigen definities
		csr: {
			zeker: 'Weet u het zeker?'
		}
	}
};

import $ from 'jquery';
import { init } from '../ctx';
import { PersistentEntity } from './api';
import Settings = DataTables.Settings;

export default {
	deferRender: true,
	createdRow(this: JQuery, tr, data: PersistentEntity) {
		$(tr).attr('data-uuid', data.UUID);
		init(tr as HTMLElement);

		$(tr)
			.children()
			.each((columnIndex, td) => {
				// Init custom buttons in rows
				$(td)
					.children('a.post')
					.each((i, a) => {
						const id = this.attr('id');
						if (!id) {
							throw new Error('Geen datatableid');
						}
						$(a).attr('data-tableid', id);
					});
			});
	},
	language: {
		buttons: {
			colvis: 'Kolom weergave',
			copy: 'Kopiëren',
			print: 'Printen',
		},
		csr: {
			zeker: 'Weet u het zeker?',
		},
		oPaginate: {
			sFirst: 'Eerste',
			sLast: 'Laatste',
			sNext: 'Volgende',
			sPrevious: 'Vorige',
		},
		sEmptyTable: 'Geen resultaten aanwezig in de tabel',
		sInfo: '_START_ tot _END_ van _TOTAL_ resultaten',
		sInfoEmpty: 'Geen resultaten om weer te geven',
		sInfoFiltered: ' (gefilterd uit _MAX_ resultaten)',
		sInfoPostFix: '',
		sInfoThousands: '.',
		sLengthMenu: '_MENU_ resultaten weergeven',
		sLoadingRecords: 'Een moment geduld aub - bezig met laden...',
		sProcessing: 'Bezig...',
		sSearch: 'Zoeken',
		sZeroRecords: 'Geen resultaten gevonden',
		select: {
			rows: {
				_: '%d rijen geselecteerd',
				0: '',
				1: '1 rij geselecteerd',
			},
		},
	},
	lengthMenu: [
		[10, 25, 50, 100, -1],
		[10, 25, 50, 100, 'Alles'],
	],
} as Settings;

import $ from 'jquery';

import { knopPost } from '../lib/knop';
import { evaluateMultiplicity } from '../lib/util';
import { replacePlaceholders } from './api';
import ButtonApi = DataTables.ButtonApi;
import ButtonsSettings = DataTables.ButtonsSettings;

declare global {
	// eslint-disable-next-line @typescript-eslint/no-namespace
	namespace DataTables {
		interface ExtButtonsSettings {
			// Default buttons, zitten om de een of andere reden niet in de typedef
			copyHtml5: ButtonSettings;
			copyFlash: ButtonSettings;
			csvHtml5: ButtonSettings;
			csvFlash: ButtonSettings;
			pdfHtml5: ButtonSettings;
			pdfFlash: ButtonSettings;
			excelHtml5: ButtonSettings;
			excelFlash: ButtonSettings;
			print: ButtonSettings;
			// Eigen buttons
			default: ButtonSettings;
			popup: ButtonSettings;
			url: ButtonSettings;
			sourceChange: ButtonSettings;
			confirm: ButtonSettings;
			defaultCollection: ButtonSettings;
		}
		// Eigen attributen op ButtonSettings, worden in DatatableKnop gezet
		interface ButtonSettings {
			href?: string;
			multiplicity?: string;
		}
	}
}

// Zet de icons van de default buttons
$.fn.dataTable.ext.buttons.copyHtml5.className += ' dt-button-ico fa-file-code';
$.fn.dataTable.ext.buttons.copyFlash.className += ' dt-button-ico fa-file-code';
$.fn.dataTable.ext.buttons.csvHtml5.className += ' dt-button-ico fa-file-csv';
$.fn.dataTable.ext.buttons.csvFlash.className += ' dt-button-ico fa-file-csv';
$.fn.dataTable.ext.buttons.pdfHtml5.className += ' dt-button-ico fa-file-pdf';
$.fn.dataTable.ext.buttons.pdfFlash.className += ' dt-button-ico fa-file-pdf';
$.fn.dataTable.ext.buttons.excelHtml5.className +=
	' dt-button-ico fa-file-excel';
$.fn.dataTable.ext.buttons.excelFlash.className +=
	' dt-button-ico fa-file-excel';
$.fn.dataTable.ext.buttons.print.className += ' dt-button-ico fa-print';

// Laat een modal zien, of doe een ajax call gebasseerd op selectie.
$.fn.dataTable.ext.buttons.default = {
	init(this: ButtonApi, dt, node, config) {
		const toggle = () => {
			this.enable(
				evaluateMultiplicity(
					config.multiplicity,
					dt.rows({ selected: true }).count()
				)
			);
		};
		dt.on('select.dt.DT deselect.dt.DT', toggle);
		// Initiele staat
		toggle();

		// Vervang :col door de waarde te vinden in de geselecteerde row
		// Dit wordt alleen geprobeerd als dit voorkomt
		if (config.href.indexOf(':') !== -1) {
			dt.on('select.dt.DT', (e, dt2, type, indexes) => {
				if (indexes.length === 1) {
					const row = dt2.row(indexes).data();
					node.attr('href', replacePlaceholders(config.href, row));
				}
			});
		}

		// Settings voor knop_ajax
		node.attr('href', config.href);
		const id = dt.tables().nodes().to$().attr('id');

		if (!id) {
			throw new Error('Datatable heeft geen id');
		}

		node.attr('data-tableid', id);
	},
	action(e, dt, button) {
		knopPost(button.get(0), e);
	},
	className: 'post loaded DataTableResponse',
};

$.fn.dataTable.ext.buttons.popup = {
	extend: 'default',
	action(e, dt, button) {
		window.open(button.attr('href'));
	},
};

$.fn.dataTable.ext.buttons.url = {
	extend: 'default',
	action(e, dt, button) {
		window.location.href = button.attr('href') ?? '';
	},
};

// Verander de bron van een datatable
// De knop is ingedrukt als de bron van de datatable
// gelijk is aan de bron van de knop.
$.fn.dataTable.ext.buttons.sourceChange = {
	init(dt, node, config) {
		const enable = () => {
			dt.buttons(node).active(dt.ajax.url() === config.href);
		};
		dt.on('xhr.sourceChange', enable);

		enable();
	},
	action(e, dt, button, config) {
		const href = config.href;

		if (!href) {
			throw new Error('SourceChange Button heeft geen href');
		}

		dt.ajax.url(href).load();
	},
};

$.fn.dataTable.ext.buttons.confirm = {
	extend: 'collection',
	init(this: ButtonApi, dt, node, config) {
		const toggle = () => {
			this.enable(
				evaluateMultiplicity(
					config.multiplicity,
					dt.rows({ selected: true }).count()
				)
			);
		};
		dt.on('select.dt.DT deselect.dt.DT', toggle);
		// Initiele staat
		toggle();

		new $.fn.dataTable.Buttons(dt, {
			buttons: [
				{
					action: config.action,
					name: 'confirm' + config.text,
					className:
						'dt-button-ico fas fa-circle-exclamation dt-button-warning',
					extend: 'default',
					href: config.href,
					multiplicity: '', // altijd mogelijk
					text: (api) => api.i18n('csr.zeker', 'Are you sure?'),
				},
			],
		} as ButtonsSettings);

		dt.button('confirm' + config.text + ':name')
			.node()
			.appendTo(config._collection);

		// Reset action to extend one.
		config.action = $.fn.dataTable.ext.buttons.collection.action;
	},
	action(e, dt, button) {
		knopPost(button.get(0), e);
	},
};

$.fn.dataTable.ext.buttons.defaultCollection = {
	extend: 'collection',
	init(dt, node, config) {
		const init = $.fn.dataTable.ext.buttons.default.init;

		if (!init) {
			throw new Error('Configuratie fout! Default knop bestaat niet');
		}

		init.call(this, dt, node, config);
	},
};

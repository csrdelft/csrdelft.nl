import $ from 'jquery';

import {knopPost} from '../knop';
import {evaluateMultiplicity} from '../util';
import ButtonApi = DataTables.ButtonApi;
import ButtonsSettings = DataTables.ButtonsSettings;

declare global {
	namespace DataTables {
		interface ExtButtonsSettings {
			// Default buttons, zitten om de een of andere reden niet in de typedef
			copyHtml5: ButtonSettings
			copyFlash: ButtonSettings
			csvHtml5: ButtonSettings
			csvFlash: ButtonSettings
			pdfHtml5: ButtonSettings
			pdfFlash: ButtonSettings
			excelHtml5: ButtonSettings
			excelFlash: ButtonSettings
			print: ButtonSettings
			// Eigen buttons
			default: ButtonSettings
			popup: ButtonSettings
			url: ButtonSettings
			sourceChange: ButtonSettings
			confirm: ButtonSettings
			defaultCollection: ButtonSettings
		}
		// Eigen attributen op ButtonSettings, worden in DatatableKnop gezet
		interface ButtonSettings {
			href?: string
			multiplicity?: string
		}
	}
}

// Zet de icons van de default buttons
$.fn.dataTable.ext.buttons.copyHtml5.className += ' dt-button-ico dt-ico-page_white_copy';
$.fn.dataTable.ext.buttons.copyFlash.className += ' dt-button-ico dt-ico-page_white_copy';
$.fn.dataTable.ext.buttons.csvHtml5.className += ' dt-button-ico dt-ico-page_white_text';
$.fn.dataTable.ext.buttons.csvFlash.className += ' dt-button-ico dt-ico-page_white_text';
$.fn.dataTable.ext.buttons.pdfHtml5.className += ' dt-button-ico dt-ico-page_white_acrobat';
$.fn.dataTable.ext.buttons.pdfFlash.className += ' dt-button-ico dt-ico-page_white_acrobat';
$.fn.dataTable.ext.buttons.excelHtml5.className += ' dt-button-ico dt-ico-page_white_excel';
$.fn.dataTable.ext.buttons.excelFlash.className += ' dt-button-ico dt-ico-page_white_excel';
$.fn.dataTable.ext.buttons.print.className += ' dt-button-ico dt-ico-printer';

// Laat een modal zien, of doe een ajax call gebasseerd op selectie.
$.fn.dataTable.ext.buttons.default = {
	init(this: ButtonApi, dt, node, config) {
		let toggle = () => {
			this.enable(
				evaluateMultiplicity(
					config.multiplicity,
					dt.rows({selected: true}).count()
				)
			);
		};
		dt.on('select.dt.DT deselect.dt.DT', toggle);
		// Initiele staat
		toggle();

		// Vervang :col door de waarde te vinden in de geselecteerde row
		// Dit wordt alleen geprobeerd als dit voorkomt
		if (config.href.indexOf(':') !== -1) {
			let replacements = /:(\w+)/g.exec(config.href)!;
			dt.on('select.dt.DT', (e, dt, type, indexes) => {
				if (indexes.length === 1) {
					let newHref = config.href;
					let row = dt.row(indexes).data();
					// skipt match, start met groepen
					for (let i = 1; i < replacements.length; i++) {
						newHref = newHref.replace(':' + replacements[i], row[replacements[i]]);
					}

					node.attr('href', newHref);
				}
			});
		}

		// Settings voor knop_ajax
		node.attr('href', config.href);
		node.attr('data-tableid', dt.tables().nodes().to$().attr('id')!);
	},
	action(e, dt, button) {
		knopPost.call(button, e);
	},
	className: 'post DataTableResponse'
};

$.fn.dataTable.ext.buttons.popup = {
	extend: 'default',
	action(e, dt, button) {
		window.open(button.attr('href'));
	}
};

$.fn.dataTable.ext.buttons.url = {
	extend: 'default',
	action(e, dt, button) {
		window.location.href = button.attr('href')!;
	}
};

// Verander de bron van een datatable
// De knop is ingedrukt als de bron van de datatable
// gelijk is aan de bron van de knop.
$.fn.dataTable.ext.buttons.sourceChange = {
	init(dt, node, config) {
		let enable = () => {
			dt.buttons(node).active(dt.ajax.url() === config.href);
		};
		dt.on('xhr.sourceChange', enable);

		enable();
	},
	action(e, dt, button, config) {
		dt.ajax.url(config.href!).load();
	}
};

$.fn.dataTable.ext.buttons.confirm = {
	extend: 'collection',
	init(this: ButtonApi, dt, node, config) {
		let toggle = () => {
			this.enable(
				evaluateMultiplicity(
					config.multiplicity,
					dt.rows({selected: true}).count()
				)
			);
		};
		dt.on('select.dt.DT deselect.dt.DT', toggle);
		// Initiele staat
		toggle();

		new $.fn.dataTable.Buttons(dt, <ButtonsSettings>{
			buttons: [
				{
					extend: 'default',
					text: (dt) => dt.i18n('csr.zeker', 'Are you sure?'),
					action: config.action,
					multiplicity: '', // altijd mogelijk
					className: 'dt-button-ico dt-ico-exclamation dt-button-warning',
					href: config.href
				}
			]
		});

		dt.buttons().container().appendTo(config._collection);

		// Reset action to extend one.
		config.action = $.fn.dataTable.ext.buttons.collection.action;
	},
	action(e, dt, button) {
		knopPost.call(button, e);
	}
};

$.fn.dataTable.ext.buttons.defaultCollection = {
	extend: 'collection',
	init(dt, node, config) {
		$.fn.dataTable.ext.buttons.default.init!.call(this, dt, node, config);
	}
};

import $ from 'jquery';
import {init} from '../ctx';
import {html} from '../util';
import {replacePlaceholders} from './api';

interface RowButtonsConfig {
	icon?: string;
	title?: string;
	action?: string;
	css?: string;
	method?: string;
}

class RowButtons {
	public static version = '1.0.0';
	public static defaults: RowButtonsConfig = {};

	private static createButtonGroup(config: RowButtonsConfig[], row: object) {
		const btnGroup = html`<div class="btn-group"></div>`;

		for (const btn of Object.values(config)) {
			const action = replacePlaceholders(btn.action!, row);

			const newButton = html`
<a href="${action}"
	class="btn btn-light noanim btn-sm DataTableRowKnop ${btn.method} ${btn.css}"
	title="${btn.title}">
		<i class="${btn.icon}"></i>
</a>`;
			btnGroup.append(newButton);
		}
		btnGroup.style.marginTop = '-10px';
		btnGroup.style.marginBottom = '-10px';
		const wrapper = html`<div class="d-inline-flex"></div>`;
		wrapper.append(btnGroup);
		init(wrapper);
		return wrapper;
	}

	private c: RowButtonsConfig;
	private s: any;

	constructor(settings: DataTables.SettingsLegacy, config: RowButtonsConfig[]) {
		const dt = new $.fn.dataTable.Api(settings as any);

		this.c = $.extend(true, {}, RowButtons.defaults, config);

		this.s = {
			dt,
			collapsedGroups: [],
			regrouping: false,
			lastDraw: null,
		};

		const dtSettings = dt.settings()[0];
		if (dtSettings._rowButtons) {
			throw new Error('RowButtons already initialized on table ' + dtSettings.nTable.id);
		}

		dtSettings._rowButtons = this;

		dt.on('draw.dt', () => {
			dt.column('actionButtons:name').nodes().each((cell: HTMLTableCellElement, index, api) => {
				cell.innerHTML = '';
				cell.append(RowButtons.createButtonGroup(config, api.row(cell).data()));
			});
		});
	}
}

declare global {
	namespace DataTables {
		// noinspection JSUnusedGlobalSymbols
		interface StaticFunctions {
			RowButtons: typeof RowButtons;
		}

		interface Settings {
			rowButtons: any;
		}
	}
}
// Expose
$.fn.dataTable.RowButtons = RowButtons;
// @ts-ignore
$.fn.DataTable.RowButtons = RowButtons;

// Attach a listener to the document which listens for DataTables initialisation
// events so we can automatically initialise
$(document).on('preInit.dt.rowButtons', (e, settings) => {
	if (e.namespace !== 'dt') {
		return;
	}

	const buttonInit = settings.oInit.rowButtons;
	const defaults = $.fn.dataTable.defaults.rowButtons;

	if (buttonInit || defaults) {
		const opts = $.extend({}, buttonInit, defaults);

		if (buttonInit !== false) {
			// tslint:disable-next-line:no-unused-expression
			new RowButtons(settings, opts);
		}
	}
});

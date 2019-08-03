import $ from 'jquery';
import {knopPost} from '../knop';
import {html} from '../util';

interface RowButtonsConfig {
	icon?: string;
	title?: string;
	action?: string;
	css?: string;
}

class RowButtons {
	public static version = '1.0.0';
	public static defaults: RowButtonsConfig = {};

	private static createButtonGroup(config: RowButtonsConfig[]) {
		const btnGroup = html`<div class="btn-group"></div>`;

		for (const btn of Object.values(config)) {
			const newButton = html`
<a href="${btn.action}"
	class="btn btn-light noanim btn-sm post DataTableRowKnop ${btn.css}"
	title="${btn.title}">
		<i class="${btn.icon}"></i>
</a>`;
			newButton.addEventListener('click', knopPost);
			btnGroup.append(newButton);
		}
		btnGroup.style.marginTop = '-10px';
		btnGroup.style.marginBottom = '-10px';
		const wrapper = html`<div class="d-flex"></div>`;
		wrapper.append(btnGroup);
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
			dt.column('actionButtons:name').nodes().each((cell: HTMLTableCellElement) => {
				cell.innerHTML = '';
				cell.append(RowButtons.createButtonGroup(config));
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

	const init = settings.oInit.rowButtons;
	const defaults = $.fn.dataTable.defaults.rowButtons;

	if (init || defaults) {
		const opts = $.extend({}, init, defaults);

		if (init !== false) {
			// tslint:disable-next-line:no-unused-expression
			new RowButtons(settings, opts);
		}
	}
});

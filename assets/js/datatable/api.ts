import $ from 'jquery';

import {init} from '../ctx';
import {parseData} from '../lib/util';
import render from './render';
import Settings = DataTables.Settings;
import ColumnSettings = DataTables.ColumnSettings;

export interface DatatableResponse {
	modal?: string;
	autoUpdate: string;
	lastUpdate: number;
	data: PersistentEntity[];
}

export interface PersistentEntity {
	UUID: string;
}

/**
 * In de backend wordt de 'export' ortogonale data gezet.
 */
declare global {
	// eslint-disable-next-line @typescript-eslint/no-namespace
	namespace DataTables {
		interface ObjectColumnData {
			export?: string;
		}
	}
}

export function isDataTableResponse(response: unknown): response is DatatableResponse {
	const check = response as DatatableResponse

	return typeof response == 'object'
		&& "lastUpdate" in check
		&& "autoUpdate" in check
		&& "data" in check
}

export async function initDataTable(el: HTMLElement): Promise<void> {
	await import('./bootstrap');

	const $el = $(el);

	const settingsJson = $el.data('settings');
	const search = $el.data('search');

	// Zet de callback voor ajax
	if (settingsJson.ajax) {
		settingsJson.ajax.data.lastUpdate = fnGetLastUpdate($el);
		settingsJson.ajax.dataSrc = fnAjaxUpdateCallback($el);
	}

	// Zet de render method op de columns
	settingsJson.columns.forEach((col: ColumnSettings) => col.render = render[col.render as string]);

	// Init DataTable
	const table = $el.DataTable(settingsJson);
	$el.dataTable().api().search(search);

	table.on('page', () => table.rows({selected: true}).deselect());
	table.on('childRow.dt', (event, data) => init(data.container.get(0)));
}

export async function initOfflineDataTable(el: HTMLElement): Promise<void> {
	await import('./bootstrap');

	$(el).DataTable(parseData(el));
}

/****************************
 * Een paar functies om met datatables te
 * praten, laadt datatables zelf niet in.
 */

export function fnUpdateDataTable(tableId: string, response: DatatableResponse): void {
	const $table = $(tableId);
	const table = $table.DataTable();
	// update or remove existing rows or add new rows
	response.data.forEach((row) => {
		const $tr = $('tr[data-uuid="' + row.UUID + '"]');
		if ($tr.length === 1) {
			if ('remove' in row) {
				table.row($tr).remove();
			} else {
				table.row($tr).data(row);
				init($tr.get(0));
			}
		} else if ($tr.length === 0) {
			table.row.add(row);
		} else {
			alert($tr.length);
		}
	});
	table.draw(false);
}

export function fnGetSelection(tableId: string): string[] {
	const selection: string[] = [];
	$(tableId + ' tbody tr.selected').each(function () {

		const uuid = $(this).attr('data-uuid');
		if (!uuid) {
			throw new Error("Tablerow heeft geen uuid")
		}
		selection.push(uuid);
	});
	return selection;
}

// new Api(selector: Settings) is valide, maar wordt als type niet geaccepteerd, type lijkt niet uit te breiden.
// https://datatables.net/reference/type/DataTables.Settings
export function getApiFromSettings(settings: Settings): DataTables.Api {
	return new $.fn.dataTable.Api(settings as unknown as string);
}

/**
 * Wordt gebruikt in gesprekken.
 *
 * @param {jQuery} $table
 */
function fnAutoScroll($table: JQuery) {
	const $scroll = $table.parent();
	if ($scroll.hasClass('dataTables_scrollBody')) {
		const top = $scroll.scrollTop()
		const height = $scroll.innerHeight()

		if (!top || !height) {
			throw new Error("$scroll heeft geen top of height")
		}
		// autoscroll if already on bottom
		if (top + height >= $scroll[0].scrollHeight - 20) {
			// check before draw and scroll after
			window.setTimeout(() => {
				$scroll.animate({
					scrollTop: $scroll[0].scrollHeight,
				}, 800);
			}, 200);
		}
	}
}

export const fnGetLastUpdate = ($table: JQuery) => (): number => Number($table.data('lastupdate'));
export const fnSetLastUpdate = ($table: JQuery) => (lastUpdate: number): JQuery => $table.data('lastupdate', lastUpdate);
/**
 * Called after ajax load complete.
 *
 * @returns object
 * @param {jQuery} $table
 */
export const fnAjaxUpdateCallback = ($table: JQuery) => (json: DatatableResponse): unknown => {
	fnSetLastUpdate($table)(json.lastUpdate);
	const tableConfig = $table.DataTable();

	if (json.autoUpdate) {
		const timeout = parseInt(json.autoUpdate, 10);
		if (!isNaN(timeout) && timeout < 600000) { // max 10 min
			setTimeout(() => {
				$.post(tableConfig.ajax.url(), {
					lastUpdate: fnGetLastUpdate($table),
				}, (data) => {
					const tableId = $table.attr('id');
					if (!tableId) {
						throw new Error("Table heeft geen id")
					}
					fnUpdateDataTable(tableId, data);
					fnAjaxUpdateCallback($table)(data);
				});
			}, timeout);
		}
	}

	fnAutoScroll($table);

	return json.data;
};

export function replacePlaceholders(action: string, row: Record<string, string>): string {
	const replacements = /:(\w+)/g.exec(action);
	if (!replacements) {
		return action;
	}

	for (let i = 1; i < replacements.length; i++) {
		action = action.replace(':' + replacements[i], row[replacements[i]]);
	}

	return action;
}

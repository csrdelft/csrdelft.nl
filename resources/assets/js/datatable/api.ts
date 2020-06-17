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
	namespace DataTables {
		interface ObjectColumnData {
			export?: string;
		}
	}
}

export async function initDataTable(el: HTMLElement) {
	await import(/*webpackChunkName: "bootstrap"*/'./bootstrap');

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

export async function initOfflineDataTable(el: HTMLElement) {
	await import(/*webpackChunkName: "bootstrap"*/'./bootstrap');

	$(el).DataTable(parseData(el));
}

/****************************
 * Een paar functies om met datatables te
 * praten, laadt datatables zelf niet in.
 */

export function fnUpdateDataTable(tableId: string, response: DatatableResponse) {
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

export function fnGetSelection(tableId: string) {
	const selection: string[] = [];
	$(tableId + ' tbody tr.selected').each(function () {
		selection.push($(this).attr('data-uuid')!);
	});
	return selection;
}

// new Api(selector: Settings) is valide, maar wordt als type niet geaccepteerd, type lijkt niet uit te breiden.
// https://datatables.net/reference/type/DataTables.Settings
export function getApiFromSettings(settings: Settings) {
	return new $.fn.dataTable.Api(settings as any);
}

/**
 * Wordt gebruikt in gesprekken.
 *
 * @param {jQuery} $table
 */
function fnAutoScroll($table: JQuery) {
	const $scroll = $table.parent();
	if ($scroll.hasClass('dataTables_scrollBody')) {
		// autoscroll if already on bottom
		if ($scroll.scrollTop()! + $scroll.innerHeight()! >= $scroll[0].scrollHeight - 20) {
			// check before draw and scroll after
			window.setTimeout(() => {
				$scroll.animate({
					scrollTop: $scroll[0].scrollHeight,
				}, 800);
			}, 200);
		}
	}
}

export const fnGetLastUpdate = ($table: JQuery) => () => Number($table.data('lastupdate'));
export const fnSetLastUpdate = ($table: JQuery) => (lastUpdate: number) => $table.data('lastupdate', lastUpdate);
/**
 * Called after ajax load complete.
 *
 * @returns object
 * @param {jQuery} $table
 */
export const fnAjaxUpdateCallback = ($table: JQuery) => (json: DatatableResponse) => {
	fnSetLastUpdate($table)(json.lastUpdate);
	const tableConfig = $table.DataTable();

	if (json.autoUpdate) {
		const timeout = parseInt(json.autoUpdate, 10);
		if (!isNaN(timeout) && timeout < 600000) { // max 10 min
			setTimeout(() => {
				$.post(tableConfig.ajax.url(), {
					lastUpdate: fnGetLastUpdate($table),
				}, (data) => {
					fnUpdateDataTable($table.attr('id')!, data);
					fnAjaxUpdateCallback($table)(data);
				});
			}, timeout);
		}
	}

	fnAutoScroll($table);

	return json.data;
};

export function replacePlaceholders(action: string, row: object) {
	const replacements = /:(\w+)/g.exec(action)!;
	if (!replacements) { return action; }

	for (let i = 1; i < replacements.length; i++) {
		action = action.replace(':' + replacements[i], row[replacements[i]]);
	}

	return action;
}

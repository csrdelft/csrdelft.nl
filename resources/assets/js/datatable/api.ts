import $ from 'jquery';

import initContext from '../context';
import Settings = DataTables.Settings;

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
				initContext($tr);
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

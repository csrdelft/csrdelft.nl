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

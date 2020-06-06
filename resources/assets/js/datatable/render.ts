import FunctionColumnRender = DataTables.FunctionColumnRender;
import moment from 'moment';
import {formatBedrag, formatFilesize} from '../util';
import {getApiFromSettings} from './api';

/**
 * Standaard gedefinieerde render functies.
 */
export default {
	default(data, type) {
		if (data === null || typeof data !== 'object') { return data; }
		switch (type) {
			case 'sort':
				return data.sort!;
			case 'export':
				return data.export!;
			default:
				return data.display!;
		}
	},
	bedrag(data) {
		return formatBedrag(data);
	},
	check(data) {
		return '<span class="ico ' + (data ? 'tick' : 'cross') + '"></span>';
	},
	aanmeldFilter(data) {
		return data ? `<span class="ico group_key" title="Aanmeld filter actief: '${data}'"></span>` : '';
	},
	aanmeldingen(data, type, row) {
		return row.aantal_aanmeldingen + ' (' + row.aanmeld_limiet + ')';
	},
	totaalPrijs(data, type, row) {
		return formatBedrag(row.aantal_aanmeldingen * parseInt(row.prijs, 10));
	},
	datetime(date, type, row) {
		// tslint:disable-next-line:triple-equals
		if (Number(date) == date) {
			return moment(date * 1000).format('L LT');
		}

		if (!date) {
			return '';
		}

		if (date.substr(0, 5) === '<time') {
			return date;
		}

		return moment(date).format('L LT');
	},
	timeago(data, type, row, meta) {
		const api = getApiFromSettings(meta.settings);
		const cell = api.cell(meta.row, meta.col).node().firstChild as HTMLTimeElement;

		switch (type) {
			case 'sort':
			case 'export':
				return cell.dateTime;
			default:
				return data;
		}
	},
	filesize(data, type) {
		switch (type) {
			case 'sort':
				return Number(data);
			default:
				return formatFilesize(data);
		}
	},
} as { [s: string]: FunctionColumnRender };

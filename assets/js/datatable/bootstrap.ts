/**
 * Knoop alle datatable plugins aan jquery.
 */
import 'datatables.net';
import 'datatables.net-autofill';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.colVis';
import 'datatables.net-buttons/js/buttons.flash';
import 'datatables.net-buttons/js/buttons.html5';
import 'datatables.net-buttons/js/buttons.print';
import 'datatables.net-colreorder';
import 'datatables.net-fixedcolumns';
import 'datatables.net-fixedheader';
import 'datatables.net-keytable';
import 'datatables.net-responsive';
import 'datatables.net-scroller';
import 'datatables.net-select';
import $ from 'jquery';
import JSZip from 'jszip';

import './dataTables.childRow';
import './dataTables.columnGroup';
import './dataTables.rowButtons';

import './buttons';
import defaults from './defaults';

declare global {
	interface Window {
		JSZip: JSZip;
	}
}

// Excel button in datatables.net-buttons/js/buttons.html5 checkt voor JSZip in window.
window.JSZip = JSZip;

$.extend(true, $.fn.dataTable.defaults, defaults);

$.fn.dataTable.ext.errMode = 'throw';

$(() => {
	$('body').on('click', () => {
		// Verwijder tooltips als de datatable modal wordt gesloten
		$('.ui-tooltip-content').parents('div').remove();
	});
});

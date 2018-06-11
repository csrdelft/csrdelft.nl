import $ from 'jquery';

import initContext from './context';

/****************************
 * Een paar functies om met datatables te
 * praten, laadt datatables zelf niet in.
 */

/**
 * @see datatable.js
 * @see view/formulier/datatable/DataTable.php
 * @param tableId
 * @param response
 */
export function fnUpdateDataTable(tableId, response) {
    let $table = $(tableId);
    let table = $table.DataTable();
    // update or remove existing rows or add new rows
    response.data.forEach((row) => {
        let $tr = $('tr[data-uuid="' + row.UUID + '"]');
        if ($tr.length === 1) {
            if ('remove' in row) {
                table.row($tr).remove();
            }
            else {
                table.row($tr).data(row);
                initContext($tr);
            }
        }
        else if ($tr.length === 0) {
            table.row.add(row);
        }
        else {
            alert($tr.length);
        }
    });
    table.draw(false);
}

/**
 * @see csrdelft.js
 * @param tableId
 * @returns {Array}
 */
export function fnGetSelection(tableId) {
    let selection = [];
    $(tableId + ' tbody tr.selected').each(function () {
        selection.push($(this).attr('data-uuid'));
    });
    return selection;
}
// Wordt na app.js geladen.

import {knopPost} from './knop';

import $ from 'jquery';
import initContext from './context';
// Excel button in datatables.net-buttons/js/buttons.html5 checkt voor JSZip in window.
import JSZip from 'jszip';
window.JSZip = JSZip;

/**
 * Knoop alle datatable plugins aan jquery.
 */
import 'datatables.net';
import 'datatables.net-autofill';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.colVis';
import 'datatables.net-buttons/js/buttons.html5';
import 'datatables.net-buttons/js/buttons.flash';
import 'datatables.net-buttons/js/buttons.print';
import 'datatables.net-colreorder';
import 'datatables.net-fixedcolumns';
import 'datatables.net-fixedheader';
import 'datatables.net-keytable';
import 'datatables.net-responsive';
import 'datatables.net-scroller';
import 'datatables.net-select';
import './lib/dataTables.childRow';
import './lib/dataTables.columnGroup';

/*!
 * csrdelft.dataTables.js
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Group by & multi-select capabilities.
 */

/**
 * Verwerk een multipliciteit in de vorm van `== 1` of `!= 0` of `> 3` voor de selecties
 *
 * @param {string} expression
 * @param {number} num
 * @returns {boolean}
 */
const evaluateMultiplicity = (expression, num) => {
    // Altijd laten zien bij geen expressie
    if (expression.length === 0){
        return true;
    }

    let operator_num = expression.split(' '),
        expression_operator = operator_num[0],
        expression_num = parseInt(operator_num[1]),
        operatorToFunction = {
        '==': (a, b) => a === b,
        '!=': (a, b) => a !== b,
        '>=': (a, b) => a >= b,
        '>': (a, b) => a > b,
        '<=': (a, b) => a <= b,
        '<': (a, b) => a < b
    };

    return operatorToFunction[expression_operator](num, expression_num);
};

$.extend(true, $.fn.dataTable.defaults, {
    deferRender: true,
    createdRow(tr, data) {
        let table = this;
        $(tr).attr('data-uuid', data.UUID);
        initContext(tr);

        $(tr).children().each((columnIndex, td) => {
            // Init custom buttons in rows
            $(td).children('a.post').each((i, a) => {
                $(a).attr('data-tableid', table.attr('id'));
            });
        });
    },
    lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, 'Alles']
    ],
    language: {
        sProcessing: 'Bezig...',
        sLengthMenu: '_MENU_ resultaten weergeven',
        sZeroRecords: 'Geen resultaten gevonden',
        sInfo: '_START_ tot _END_ van _TOTAL_ resultaten',
        sInfoEmpty: 'Geen resultaten om weer te geven',
        sInfoFiltered: ' (gefilterd uit _MAX_ resultaten)',
        sInfoPostFix: '',
        sSearch: 'Zoeken',
        sEmptyTable: 'Geen resultaten aanwezig in de tabel',
        sInfoThousands: '.',
        sLoadingRecords: 'Een moment geduld aub - bezig met laden...',
        oPaginate: {
            sFirst: 'Eerste',
            sLast: 'Laatste',
            sNext: 'Volgende',
            sPrevious: 'Vorige'
        },
        select: {
            rows: {
                '_': '%d rijen geselecteerd',
                '0': '',
                '1': '1 rij geselecteerd'
            }
        },
        buttons: {
            copy: 'Kopiëren',
            print: 'Printen',
            colvis: 'Kolom weergave'
        },
        // Eigen definities
        csr: {
            zeker: 'Weet u het zeker?'
        }
    }
});

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
    init(dt, node, config) {
        let that = this;
        let toggle = function () {
            that.enable(
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
            let replacements = /:(\w+)/g.exec(config.href);
            dt.on('select.dt.DT', (e, dt, type, indexes) => {
                if (indexes.length === 1) {
                    let newHref = config.href;
                    let row = dt.row(indexes).data();
                    // skipt match, start met groepen
                    for (let i = 1; i < replacements.length; i++) {
                        newHref = newHref.replace(':' + replacements[i], row[replacements[i]]);
                    }

                    node.attr('href', newHref)
                }
            });
        }

        // Settings voor knop_ajax
        node.attr('href', config.href);
        node.attr('data-tableid', dt.context[0].sTableId);
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
        window.location.href = button.attr('href');
    }
};

// Verander de bron van een datatable
// De knop is ingedrukt als de bron van de datatable
// gelijk is aan de bron van de knop.
$.fn.dataTable.ext.buttons.sourceChange = {
    init(dt, node, config) {
        let enable = function () {
            dt.buttons(node).active(dt.ajax.url() === config.href);
        };
        dt.on('xhr.sourceChange', enable);

        enable();
    },
    action(e, dt, button, config) {
        dt.ajax.url(config.href).load();
    }
};

$.fn.dataTable.ext.buttons.confirm = {
    extend: 'collection',
    init: function (dt, node, config) {
        let that = this;
        let toggle = () => {
            that.enable(
                evaluateMultiplicity(
                    config.multiplicity,
                    dt.rows({selected: true}).count()
                )
            );
        };
        dt.on('select.dt.DT deselect.dt.DT', toggle);
        // Initiele staat
        toggle();

        let action = config.action;

        let buttons = new $.fn.dataTable.Buttons(dt, {
            buttons: [
                {
                    extend: 'default',
                    text: dt => dt.i18n('csr.zeker', 'Are you sure?'),
                    action,
                    multiplicity: '', // altijd mogelijk
                    className: 'dt-button-ico dt-ico-exclamation dt-button-warning',
                    href: config.href
                }
            ]
        });

        config._collection.append(buttons.dom.container.children());

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
        $.fn.dataTable.ext.buttons.default.init.call(this, dt, node, config);
    }
};

$.fn.dataTable.render.bedrag = (data) => '€' + (data / 100).toFixed(2);

$.fn.dataTable.render.check = (data) => {
    return '<span class="ico ' + (data ? 'tick' : 'cross') + '"></span>';
};

$.fn.dataTable.render.aanmeldFilter = data => {
    return data ? `<span class="ico group_key" title="Aanmeld filter actief: '${data}'"></span>` : '';
};

$.fn.dataTable.render.aanmeldingen = (data, type, row) => {
    return row.aantal_aanmeldingen + ' (' + row.aanmeld_limiet + ')';
};

$.fn.dataTable.render.totaalPrijs = (data, type, row) => {
    return $.fn.dataTable.render.bedrag(row.aantal_aanmeldingen * parseInt(row.prijs));
};

$(function () {
    $('body').on('click', () => {
        // Verwijder tooltips als de datatable modal wordt gesloten
        $(".ui-tooltip-content").parents('div').remove();
    });
});

/**
 * Wordt gebruikt in gesprekken.
 *
 * @see view/formulier/datatable/DataTable.php
 * @param tableId
 */
window.fnAutoScroll = function(tableId) {
    let $table = $(tableId);
    let $scroll = $table.parent();
    if ($scroll.hasClass('dataTables_scrollBody')) {
        // autoscroll if already on bottom
        if ($scroll.scrollTop() + $scroll.innerHeight() >= $scroll[0].scrollHeight - 20) {
            // check before draw and scroll after
            window.setTimeout(() => {
                $scroll.animate({
                    scrollTop: $scroll[0].scrollHeight
                }, 800);
            }, 200);
        }
    }
};

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

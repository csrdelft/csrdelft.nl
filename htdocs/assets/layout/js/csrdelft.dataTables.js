/*!
 * csrdelft.dataTables.js
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Group by & multi-select capabilities.
 */

$(document).ready(function () {
	fnInitDataTables();
});


function fnInitDataTables() {
	// Custom global filter
	$.fn.dataTable.ext.search.push(fnGroupExpandCollapseDraw);

    // Verwerk een multipliciteit in de vorm van `== 1` of `!= 0` of `> 3` voor de selecties
	// Returns bool
    var evaluateMultiplicity = function (expression, num) {
    	// Altijd laten zien bij geen expressie
    	if (expression.length === 0) return true;
        var operator_num = expression.split(' ');
        return {
            '==': function (a, b) { return a == b; },
            '!=': function (a, b) { return a != b; },
            '>=': function (a, b) { return a >= b; },
            '>' : function (a, b) { return a >  b; },
            '<=': function (a, b) { return a <= b; },
            '<' : function (a, b) { return a <  b; }
        }[operator_num[0]](num, parseInt(operator_num[1]));
    };

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
        init: function (dt, node, config) {
            var that = this;
            var toggle = function () {
				that.enable(
					evaluateMultiplicity(
						config.multiplicity,
						dt.rows({selected: true}).count()))
			};
            dt.on('select.dt.DT deselect.dt.DT', toggle);
            // Initiele staat
            toggle();

            // Settings voor knop_ajax
            node.attr('href', config.href);
            node.attr('data-tableid', dt.context[0].sTableId);
        },
        action: function( e, dt, button, config ) {
            knop_post.call(button, e)
        },
        className: 'post DataTableResponse'
    };

    // Verander de bron van een datatable
	// De knop is ingedrukt als de bron van de datatable
	// gelijk is aan de bron van de knop.
	$.fn.dataTable.ext.buttons.sourceChange = {
		init: function (dt, node, config) {
			var enable = function () {
				dt.buttons(node).active(dt.ajax.url() == config.href);
			};
			dt.on('xhr.sourceChange', enable);

			enable();
		},
		action: function (e, dt, button, config) {
			dt.ajax.url(config.href).load();
		}
	};

	$.fn.dataTable.ext.buttons.confirm = {
		extend: 'collection',
		init: function (dt, node, config) {
			var that = this;
			var toggle = function () {
				that.enable(
					evaluateMultiplicity(
						config.multiplicity,
						dt.rows({selected: true}).count()))
			};
			dt.on('select.dt.DT deselect.dt.DT', toggle);
			// Initiele staat
			toggle();

			var action = config.action;

			var buttons = new $.fn.dataTable.Buttons( dt, {
				buttons: [
					{
						extend: 'default',
						text: function ( dt ) {
							return dt.i18n( 'csr.zeker', 'Are you sure?' );
						},
						action: action,
						multiplicity: '', // altijd mogelijk
						className: 'dt-button-ico dt-ico-exclamation dt-button-warning',
						href: config.href
					}
				]
			} );

			config._collection.append(buttons.dom.container.children());

			// Reset action to extend one.
			config.action = $.fn.dataTable.ext.buttons.collection.action;
		},
		action: function( e, dt, button, config ) {
			knop_post.call(button, e)
		},
	};

	$('body').on('click', function () {
		// Verwijder tooltips als de datatable modal wordt gesloten
		$(".ui-tooltip-content").parents('div').remove();
	})
}

function fnAutoScroll(tableId) {
	var $table = $(tableId);
	var $scroll = $table.parent();
	if ($scroll.hasClass('dataTables_scrollBody')) {
		// autoscroll if already on bottom
		if ($scroll.scrollTop() + $scroll.innerHeight() >= $scroll[0].scrollHeight - 20) {
			// check before draw and scroll after
			window.setTimeout(function () {
				$scroll.animate({
					scrollTop: $scroll[0].scrollHeight
				}, 800);
			}, 200);
		}
	}
}

function fnUpdateDataTable(tableId, response) {
	var $table = $(tableId);
	var table = $table.DataTable();
	// update or remove existing rows or add new rows
	response.data.forEach(function (row) {
		var $tr = $('tr[data-uuid="' + row.UUID + '"]');
		if ($tr.length === 1) {
			if ('remove'in row) {
				table.row($tr).remove();
			}
			else {
				table.row($tr).data(row);
				init_context($tr);
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

function fnGetSelection(tableId) {
	var selection = [];
	$(tableId + ' tbody tr.selected').each(function () {
		selection.push($(this).attr('data-uuid'));
	});
	return selection;
}

function fnGetGroupByColumn(tableId) {
	var $table = $(tableId);
	var columnId = parseInt($table.attr('groupbycolumn'));
	if (isNaN(columnId)) {
		return false;
	}
	return columnId;
}

function fnGroupByColumn(event, settings) {
	if (!bCtrlPressed) {
		return;
	}
	var $table = $(settings.nTable);
	if ($table.data('regrouping')) {
		return; // prevent loop
	}
	var table = $table.DataTable();
	var columnId = fnGetGroupByColumn($table);
	var newOrder = table.order();
	table.column(columnId).visible(true);
	columnId = newOrder[0][0];
	table.column(columnId).visible(false);
	$table.attr('groupbycolumn', columnId);
	$table.data('collapsedGroups', []);
	$('thead tr th:first', $table).addClass('toggle-group  toggle-group-expanded');
	settings.aaSortingFixed = newOrder.slice(); // copy by value
	$table.data('regrouping', true);
	table.draw(false);
}

function fnGroupByColumnDraw(event, settings) {
	var $table = $(settings.nTable);
	if ($table.data('lastDraw') === Date.now()) {
		return; // workaround childrow
	}
	if ($table.data('regrouping')) {
		$table.data('regrouping', false);
		return; // prevent loop
	}
	var groupById = fnGetGroupByColumn($table);
	if (groupById === false) {
		return;
	}
	if (!$table.data('collapsedGroups')) {
		return; // wait for init
	}
	var collapse = $table.data('collapsedGroups').slice(); // copy by value
	var colspan = '';
	var j = $('thead tr th', $table).length - 2;
	for (var i = 0; i < j; i++) {
		colspan += '<td></td>';
	}
	var groupRow;
	if (settings.aiDisplay.length > 0) {
		// Create group rows for visible rows
		var table = $table.DataTable();
		var rows = $(table.rows({page: 'current'}).nodes());
		var last = null;
		// Iterate over data in the group by column
		table.column(groupById, {page: 'current'}).data().each(function (group, i) {
			if (last !== group) {
				// Create group rows for collapsed groups
				while (collapse.length > 0 && collapse[0].localeCompare(group) < 0) {
					groupRow = $('<tr class="group"><td class="toggle-group"></td><td class="group-label">' + collapse[0] + '</td>' + colspan + '</tr>').data('groupData', collapse[0]);
					rows.eq(i).before(groupRow);
					collapse.shift();
				}
				groupRow = $('<tr class="group"><td class="toggle-group toggle-group-expanded"></td><td class="group-label">' + group + '</td>' + colspan + '</tr>').data('groupData', group);
				rows.eq(i).before(groupRow);
				last = group;
			}
		});
	}
	// Create group rows for collapsed groups
	var tbody = $table.children('tbody:first');
	collapse.forEach(function (group) {
		groupRow = $('<tr class="group"><td class="toggle-group"></td><td class="group-label">' + group + '</td>' + colspan + '</tr>').data('groupData', group);
		tbody.append(groupRow);
	});
	$table.data('lastDraw', Date.now());
}

function fnHideEmptyCollapsedAll(tableId, $th) {
	var $table = $(tableId);
	if ($('tr.group', $table).length == $table.data('collapsedGroups').length) {
		$('td.dataTables_empty', $table).parent().remove();
		$th.removeClass('toggle-group-expanded');
	}
	else {
		$th.addClass('toggle-group-expanded');
	}
}

function fnGroupExpandCollapse(tableId, $tr) {
	var $table = $(tableId);
	var table = $table.DataTable();
	var collapse = $table.data('collapsedGroups');
	var td = $('td:first', $tr);
	td.toggleClass('toggle-group-expanded');
	var group = $tr.data('groupData');
	if (td.hasClass('toggle-group-expanded')) {
		collapse = $.grep(collapse, function (value) {
			return value !== group;
		});
	}
	else {
		collapse.push(group);
	}
	$table.data('collapsedGroups', collapse.sort());
	table.draw(false);
	fnHideEmptyCollapsedAll($table, $('thead tr th:first', $table));
}

function fnGroupExpandCollapseAll(tableId, $th) {
	var $table = $(tableId);
	var table = $table.DataTable();
	var columnId = fnGetGroupByColumn($table);
	if (columnId === false) {
		return;
	}
	var collapse = [];
	if ($th.hasClass('toggle-group-expanded')) {
		var last = null;
		table.column(columnId).data().each(function (group, i) {
			if (last !== group) {
				collapse.push(group);
				last = group;
			}
		});
	}
	$table.data('collapsedGroups', collapse);
	table.draw(false);
	fnHideEmptyCollapsedAll($table, $th);
}

function fnGroupExpandCollapseDraw(settings, data, index) {
	var table = $(settings.nTable);
	var columnId = fnGetGroupByColumn(table);
	if (columnId === false) {
		return true;
	}
	var group = data[columnId];
	var collapse = table.data('collapsedGroups');
	if ($.inArray(group, collapse) > -1) {
		return false;
	}
	return true;
}

function fnChildRow(tableId, $td, column) {
	var tr = $td.closest('tr');
	var row = $(tableId).DataTable().row(tr);
	if (row.child.isShown()) {
		if (tr.hasClass('loading')) {
			// TODO: abort ajax
		}
		else {
			var innerDiv = tr.next().children(':first').children(':first');
			innerDiv.slideUp(400, function () {
				row.child.hide();
				tr.removeClass('expanded');
			});
		}
	}
	else {
		row.child('<div class="innerDetails verborgen"></div>').show();
		tr.addClass('expanded loading');
		var innerDiv = tr.next().addClass('childrow').children(':first').children(':first');
		var jqXHR = $.ajax({
			url: $td.data('detailSource')
		});
		jqXHR.done(function (data, textStatus, jqXHR) {
			if (row.child.isShown()) {
				tr.removeClass('loading');
				innerDiv.html(data).slideDown();
				init_context(innerDiv);
			}
		});
		jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
			if (row.child.isShown()) {
				tr.removeClass('loading');
				tr.find('td.toggle-childrow').html('<img title="' + errorThrown + '" src="/plaetjes/famfamfam/cancel.png" />');
			}
		});
	}
}

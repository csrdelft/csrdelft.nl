/*!
 * csrdelft.dataTables.js
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Group by & multi-select capabilities.
 */

/**
 * Fix recursion on draw inside order callback
 * @type Boolean
 */
var bOrderDraw = false;

$(document).ready(function() {
	fnInitDataTables();
});

function fnInitDataTables() {
	// Default global settings
	$.extend($.fn.dataTable.defaults, {
		"dom": 'frtpli',
		"lengthMenu": [[10, 15, 25, 50, 100, -1], [10, 15, 25, 50, 100, "Alles"]],
	});
	// Custom global filter
	$.fn.dataTable.ext.search.push(fnGroupExpandCollapseDraw);
}

function fnMultiSelect(tr) {
	if (tr.hasClass('group')) {
		if (tr.nextUntil('.group').not('.selected').length !== 0) {
			if (!bShiftPressed) {
				tr.siblings('.selected').removeClass('selected');
			}
			tr.nextUntil('.group').addClass('selected');
		}
		else {
			tr.nextUntil('.group').removeClass('selected');
		}
	}
	else if (!tr.children(':first').hasClass('dataTables_empty')) {
		tr.toggleClass('selected');
		if (bShiftPressed) {
			var selected = tr.hasClass('selected');
			if (tr.prevAll('.selected').not('.group').length !== 0) {
				tr.prevUntil('.selected').not('.group').each(function() {
					$(this).toggleClass('selected', selected);
				});
			}
			else if (tr.nextAll('.selected').not('.group').length !== 0) {
				tr.nextUntil('.selected').not('.group').each(function() {
					$(this).toggleClass('selected', selected);
				});
			}
		}
	}
}

function fnGetGroupByColumn(table) {
	var columnId = parseInt(table.attr('groupByColumn'));
	if (isNaN(columnId)) {
		return false;
	}
	return columnId;
}

function fnGroupByColumn(e, settings) {
	if (bOrderDraw || !bCtrlPressed) {
		return;
	}
	var table = $(settings.nTable);
	var dataTable = table.DataTable();
	var columnId = fnGetGroupByColumn(table);
	var newOrder = dataTable.order();
	dataTable.column(columnId).visible(true);
	columnId = newOrder[0][0];
	dataTable.column(columnId).visible(false);
	table.attr('groupByColumn', columnId);
	table.data('collapsedGroups', []);
	settings.aaSortingFixed = newOrder.slice(); // copy by value
	bOrderDraw = true;
	dataTable.draw();
}

function fnGroupByColumnDraw(e, settings) {
	if (bOrderDraw) {
		bOrderDraw = false;
		return;
	}
	var table = $(settings.nTable);
	var columnId = fnGetGroupByColumn(table);
	if (columnId === false) {
		return;
	}
	var collapse = table.data('collapsedGroups').slice(); // copy by value
	var colspan = settings.aoColumns.length - 1;
	var groupRow;
	if (settings.aiDisplay.length > 0) {
		// Create group rows for visible rows
		var dataTable = table.DataTable();
		var rows = $(dataTable.rows({page: 'current'}).nodes());
		var last = null;
		dataTable.column(columnId, {page: 'current'}).data().each(function(group, i) {
			if (last !== group) {
				// Create group rows for collapsed groups
				while (collapse.length > 0 && collapse[0].localeCompare(group) < 0) {
					groupRow = $('<tr class="group"><td class="details-control"></td><td colspan="' + colspan + '">' + collapse[0] + '</td></tr>').data('groupData', collapse[0]);
					rows.eq(i).before(groupRow);
					collapse.shift();
				}
				groupRow = $('<tr class="group expanded"><td class="details-control"></td><td colspan="' + colspan + '">' + group + '</td></tr>').data('groupData', group);
				rows.eq(i).before(groupRow);
				last = group;
			}
		});
	}
	// Create group rows for collapsed groups
	var tbody = table.children('tbody:first');
	collapse.forEach(function(group) {
		groupRow = $('<tr class="group"><td class="details-control"></td><td colspan="' + colspan + '">' + group + '</td></tr>').data('groupData', group);
		tbody.append(groupRow);
	});
}

function fnGroupExpandCollapse(dataTable, tr, td) {
	var table = tr.parent().parent();
	var collapse = table.data('collapsedGroups');
	tr.toggleClass('expanded');
	var group = tr.data('groupData');
	if (tr.hasClass('expanded')) {
		collapse = $.grep(collapse, function(value) {
			return value !== group;
		});
	}
	else {
		collapse.push(group);
	}
	table.data('collapsedGroups', collapse.sort());
	bCtrlPressed = false; // prevent order callback
	dataTable.draw();
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

function fnChildRow(dataTable, td) {
	var tr = td.closest('tr');
	if (tr.hasClass('group')) {
		fnGroupExpandCollapse(dataTable, tr, td);
		return;
	}
	var row = dataTable.row(tr);
	if (row.child.isShown()) {
		if (tr.hasClass('loading')) {
			// TODO: abort ajax
		}
		else {
			tr.removeClass('expanded');
			var innerDiv = tr.next().children(':first').children(':first');
			innerDiv.slideUp(400, function() {
				row.child.hide();
			});
		}
	}
	else {
		row.child('<div class="innerDetails" style="display: none;"></div>').show();
		tr.addClass('expanded loading');
		var innerDiv = tr.next().addClass('childrow').children(':first').children(':first');
		$.ajax({
			url: td.attr('detailSource')
		}).done(function(data) {
			if (row.child.isShown()) {
				tr.removeClass('loading');
				innerDiv.html(data).slideDown();
			}
		});
	}
}

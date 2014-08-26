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
	table.removeClass('collapseAll');
	table.data('expandedGroups', []);
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
	// Create group rows for visible rows
	var dataTable = table.DataTable();
	var rows = dataTable.rows({page: 'current'}).nodes();
	if (rows.length < 1) {
		console.log(rows.length);
		return;
	}
	var firstRow = $(rows).first();
	var groupRow;
	var colspan = settings.aoColumns.length - 1;
	var collapse = table.data('collapsedGroups');
	collapse.forEach(function(group) {
		groupRow = $('<tr class="group"><td class="details-control"></td><td colspan="' + colspan + '">' + group + '</td></tr>').data('groupData', group);
		firstRow.before(groupRow);
	});
	var last = null;
	dataTable.column(columnId, {page: 'current'}).data().each(function(group, i) {
		if (last !== group) {
			groupRow = $('<tr class="group expanded"><td class="details-control"></td><td colspan="' + colspan + '">' + group + '</td></tr>').data('groupData', group);
			$(rows).eq(i).before(groupRow);
			last = group;
		}
	});
}

function fnGroupExpandCollapse(dataTable, tr, td) {
	var table = tr.parent().parent();
	var expand = table.data('expandedGroups');
	var collapse = table.data('collapsedGroups');
	tr.toggleClass('expanded');
	var group = tr.data('groupData');
	if (table.hasClass('collapseAll')) {
		if (tr.hasClass('expanded')) {
			expand.push(group);
		}
		else {
			expand = $.grep(expand, function(value) {
				return value !== group;
			});
		}
	}
	else {
		if (tr.hasClass('expanded')) {
			collapse = $.grep(collapse, function(value) {
				return value !== group;
			});
		}
		else {
			collapse.push(group);
		}
	}
	table.data('expandedGroups', expand);
	table.data('collapsedGroups', collapse);
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
	if (table.hasClass('collapseAll')) {
		var expand = table.data('expandedGroups');
		if ($.inArray(group, expand) > -1) {
			return true;
		}
		return false;
	}
	else {
		var collapse = table.data('collapsedGroups');
		if ($.inArray(group, collapse) > -1) {
			return false;
		}
		return true;
	}
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

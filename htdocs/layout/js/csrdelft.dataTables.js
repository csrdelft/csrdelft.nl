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

$(document).ready(function () {
	fnInitDataTables();
});

function fnInitDataTables() {
	// Custom global filter
	$.fn.dataTable.ext.search.push(fnGroupExpandCollapseDraw);
}

function fnGetSelectionSize(tableId) {
	return $(tableId + ' tbody tr.selected').length;
}

function fnGetSelection(tableId) {
	var selection = [];
	$(tableId + ' tbody tr.selected').each(function () {
		selection.push($(this).attr('data-objectid'));
	});
	return selection;
}

function fnGetSelectedObjectId(tableId) {
	return $(tableId + ' tbody tr.selected:first').attr('data-objectid');
}

function fnGetGroupByColumn(table) {
	var columnId = parseInt(table.attr('groupbycolumn'));
	if (isNaN(columnId)) {
		return false;
	}
	return columnId;
}

function fnGroupByColumn(event, settings) {
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
	table.attr('groupbycolumn', columnId);
	table.data('collapsedGroups', []);
	table.find('thead tr th').first().addClass('toggle-group  toggle-group-expanded');
	settings.aaSortingFixed = newOrder.slice(); // copy by value
	bOrderDraw = true;
	dataTable.draw();
}

function fnGroupByColumnDraw(event, settings) {
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
	var colspan = '';
	var j = $('th', table).length - 2;
	for (var i = 0; i < j; i++) {
		colspan += '<td></td>';
	}
	var groupRow;
	if (settings.aiDisplay.length > 0) {
		// Create group rows for visible rows
		var dataTable = table.DataTable();
		var rows = $(dataTable.rows({page: 'current'}).nodes());
		var last = null;
		dataTable.column(columnId, {page: 'current'}).data().each(function (group, i) {
			if (last !== group) {
				// Create group rows for collapsed groups
				while (collapse.length > 0 && collapse[0].localeCompare(group) < 0) {
					groupRow = $('<tr class="group"><td class="toggle-group"></td><td>' + collapse[0] + '</td>' + colspan + '</tr>').data('groupData', collapse[0]);
					rows.eq(i).before(groupRow);
					collapse.shift();
				}
				groupRow = $('<tr class="group expanded"><td class="toggle-group toggle-group-expanded"></td><td>' + group + '</td>' + colspan + '</tr>').data('groupData', group);
				rows.eq(i).before(groupRow);
				last = group;
			}
		});
	}
	// Create group rows for collapsed groups
	var tbody = table.children('tbody:first');
	collapse.forEach(function (group) {
		groupRow = $('<tr class="group"><td class="toggle-group"></td><td>' + group + '</td>' + colspan + '</tr>').data('groupData', group);
		tbody.append(groupRow);
	});
}

function fnGroupExpandCollapse(dataTable, table, tr) {
	var collapse = table.data('collapsedGroups');
	tr.toggleClass('expanded');
	var group = tr.data('groupData');
	if (tr.hasClass('expanded')) {
		collapse = $.grep(collapse, function (value) {
			return value !== group;
		});
	}
	else {
		collapse.push(group);
	}
	table.data('collapsedGroups', collapse.sort());
	bCtrlPressed = false; // prevent order callback weird effect
	dataTable.draw();
}

function fnGroupExpandCollapseAll(dataTable, table, tr) {
	var columnId = fnGetGroupByColumn(table);
	if (columnId === false) {
		return;
	}
	tr.toggleClass('expanded');
	var collapse = [];
	if (!tr.hasClass('expanded')) {
		var last = null;
		dataTable.column(columnId).data().each(function (group, i) {
			if (last !== group) {
				collapse.push(group);
				last = group;
			}
		});
	}
	table.data('collapsedGroups', collapse);
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

function fnChildRow(dataTable, td, column) {
	var tr = td.closest('tr');
	var row = dataTable.row(tr);
	if (row.child.isShown()) {
		if (tr.hasClass('loading')) {
			// TODO: abort ajax
		}
		else {
			tr.removeClass('expanded');
			var innerDiv = tr.next().children(':first').children(':first');
			innerDiv.slideUp(400, function () {
				row.child.hide();
			});
		}
	}
	else if (typeof column === 'string') { // TODO: preloaded expand
		row.child('<div class="innerDetails verborgen"></div>').show();
		tr.addClass('expanded');
		var innerDiv = tr.next().addClass('childrow').children(':first').children(':first');
		innerDiv.html(data).slideDown();
	}
	else {
		row.child('<div class="innerDetails verborgen"></div>').show();
		tr.addClass('expanded loading');
		var innerDiv = tr.next().addClass('childrow').children(':first').children(':first');
		$.ajax({
			url: td.data('detailSource')
		}).done(function (data) {
			if (row.child.isShown()) {
				tr.removeClass('loading');
				innerDiv.html(data).slideDown();
			}
		});
	}
}

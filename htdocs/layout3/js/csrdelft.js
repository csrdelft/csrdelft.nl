/*!
 * csrdelft.js
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De stek layout uit 2014
 */

/**
 * Don't rely on event.shiftKey
 * @type Boolean
 */
var shiftPressed;
/**
 * Don't rely on event.ctrlKey
 * @type Boolean
 */
var ctrlPressed;
/**
 * Determine if an order.dt events originates from a draw()
 * @type Boolean
 */
var orderDraw;

/* Init functions */

$(document).ready(function() {
	init_keyPressed();
	init_timeago();
	init_dataTables();
});

function init_timeago() {
	$.timeago.settings.strings = {
		prefiprefixAgo: "",
		prefixFromNow: "sinds",
		suffixAgo: "geleden",
		suffixFromNow: "",
		seconds: "minder dan een minuut",
		minute: "1 minuut",
		minutes: "%d minuten",
		hour: "1 uur",
		hours: "%d uur",
		day: "een dag",
		days: "%d dagen",
		month: "een maand",
		months: "%d maanden",
		year: "een jaar",
		years: "%d jaar",
		wordSeparator: " ",
		numbers: []
	};
}

function init_keyPressed() {
	$(window).on('keyup', function(event) {
		$(window).one('keydown', function(event) {
			if (event.which === 16) { // shift
				shiftPressed = true;
			}
			else if (event.which === 17) { // ctrl
				ctrlPressed = true;
			}
		});
		if (event.which === 16) { // shift
			shiftPressed = false;
		}
		else if (event.which === 17) { // ctrl
			ctrlPressed = false;
		}
	});
	$(window).trigger('keyup');
}


/* DataTables */

function init_dataTables() {
	// Default global settings
	$.extend($.fn.dataTable.defaults, {
		"dom": 'frtpli',
		"lengthMenu": [[10, 15, 25, 50, 100, -1], [10, 15, 25, 50, 100, "Alles"]],
	});
	// Custom global filter
	$.fn.dataTable.ext.search.push(groupExpandCollapseDraw);
}

function multiSelect(tr) {
	if (tr.hasClass('group')) {
		if (tr.nextUntil('.group').not('.selected').length !== 0) {
			if (!shiftPressed) {
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
		if (shiftPressed) {
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

function setGroupByColumn(table, columnId) {
	console.log(columnId);
	table.data('groupByColumn', columnId);
	if (columnId === false) {
		table.removeClass('collapseAll');
		table.data('expandedGroups', []);
		table.data('collapsedGroups', []);
	}
}
function getGroupByColumn(table) {
	var columnId = parseInt(table.data('groupByColumn'));
	if (isNaN(columnId)) {
		return false;
	}
	return columnId;
}
function groupByColumn(e, settings) {
	if (orderDraw) {
		orderDraw = false;
		return;
	}
	var table = $(settings.nTable);
	var api = table.DataTable();
	var columnId = getGroupByColumn(table);
	if (ctrlPressed) {
		api.column(columnId).visible(true);
		columnId = api.order()[0][0];
		api.column(columnId).visible(false);
	}
	else if (!shiftPressed) {
		api.column(columnId).visible(true);
		columnId = false;
	}
	setGroupByColumn(table, columnId);
}
function groupByColumnDraw(e, settings) {
	var table = $(settings.nTable);
	var api = table.DataTable();
	var columnId = getGroupByColumn(table);
	if (columnId === false) {
		return;
	}
	// Create group rows for visible rows
	var rows = api.rows({page: 'current'}).nodes();
	var last = null;
	api.column(columnId, {page: 'current'}).data().each(function(group, i) {
		if (last !== group) {
			var colspan = settings.aoColumns.length - 1;
			var expanded;
			// TODO: if($.inArray(group, table.data('collapsedGroups'))) {
			var html = '<tr class="group expanded"><td class="details-control"></td><td colspan="' + colspan + '">' + group + '</td></tr>';
			$(rows).eq(i).before(html);
			last = group;
		}
	});
}
function groupExpandCollapse(dataTable, tr, td) {
	var table = tr.parent().parent();
	var expand = table.data('expandedGroups');
	var collapse = table.data('collapsedGroups');
	if (ctrlPressed) {
		table.toggleClass('collapseAll');
		expand = [];
		collapse = [];
	}
	else {
		tr.toggleClass('expanded');
	}
	var group = td.next().html();
	if (table.hasClass('collapseAll')) {
		if (tr.hasClass('expanded')) {
			expand.push(group);
		}
		else {
			expand = $.grep(expand, function(value) {
				return value !== group;
			});
		}
		if (expand.length < 1) {
			table.removeClass('collapseAll');
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
	orderDraw = true;
	dataTable.draw();
}
function groupExpandCollapseDraw(settings, data, index) {
	var table = $(settings.nTable);
	var columnId = getGroupByColumn(table);
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

function childRow(dataTable, td) {
	var tr = td.closest('tr');
	if (tr.hasClass('group')) {
		groupExpandCollapse(dataTable, tr, td);
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
			url: td.attr('href')
		}).done(function(data) {
			if (row.child.isShown()) {
				tr.removeClass('loading');
				innerDiv.html(data).slideDown();
			}
		});
	}
}

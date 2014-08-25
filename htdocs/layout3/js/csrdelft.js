/*!
 * csrdelft.js
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * De stek layout uit 2014
 */

var shiftPressed = false;
var ctrlPressed = false;

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
	$(window).keydown(function(event) {
		if (event.which === 16) { // shift
			shiftPressed = true;
		}
		else if (event.which === 17) { // ctrl
			ctrlPressed = true;
		}
	});
	$(window).keyup(function(event) {
		if (event.which === 16) { // shift
			shiftPressed = false;
		}
		else if (event.which === 17) { // ctrl
			ctrlPressed = false;
		}
	});
}


/* DataTables */

function init_dataTables() {
	// Default global settings
	$.extend($.fn.dataTable.defaults, {
		"dom": 'frtpli',
		"lengthMenu": [[10, 15, 25, 50, 100, -1], [10, 15, 25, 50, 100, "Alles"]],
	});
	// Custom global filter
	$.fn.dataTable.ext.search.push(
			function(settings, data, index) {
				var table = settings.nTable;
				if ($(table).hasClass('groupByColumn')) {
					// TODO return false;
				}
				return true;
			}
	);
	// Run initialisation function
	$('table.init').each(function() {
		var tableId = $(this).attr('id');
		if (tableId) {
			var init = 'init_' + tableId;
			if (typeof window[init] === 'function') {
				window[init]();
				$(this).removeClass('init');
			}
		}
	});
}

function multiSelect(dataTable, tr) {
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
	else {
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

function setGroupByColumn(table, column) {
	$(table).attr('groupByColumn', column);
}
function getGroupByColumn(table) {
	var groupByColumn = parseInt($(table).attr('groupByColumn'));
	if (isNaN(groupByColumn)) {
		return false;
	}
	return groupByColumn;
}
function groupByColumn(dataTable, settings) {
	var groupByColumn = getGroupByColumn(settings.nTable);
	if (ctrlPressed) {
		dataTable.column(groupByColumn).visible(true);
		groupByColumn = dataTable.order()[0][0];
		dataTable.column(groupByColumn).visible(false);
	}
	else if (!shiftPressed) {
		dataTable.column(groupByColumn).visible(true);
		groupByColumn = false;
	}
	setGroupByColumn(settings.nTable, groupByColumn);
}
function groupByColumnDraw(dataTable, settings) {
	var groupByColumn = getGroupByColumn(settings.nTable);
	if (groupByColumn === false) {
		return;
	}
	// Create group rows
	var rows = dataTable.rows({page: 'current'}).nodes();
	var last = null;
	dataTable.column(groupByColumn, {page: 'current'}).data().each(function(group, i) {
		if (last !== group) {
			var colspan = settings.aoColumns.length - 1;
			var html = '<tr class="group expanded"><td class="details-control"></td><td colspan="' + colspan + '">' + group + '</td></tr>';
			$(rows).eq(i).before(html);
			last = group;
		}
	});
}
function groupExpandCollapse(dataTable, td, tr) {
	
}

function childRow(dataTable, td) {
	var tr = td.closest('tr');
	if (tr.hasClass('group')) {
		return groupExpandCollapse(dataTable, td, tr);
	}
	var row = dataTable.row(tr);
	if (row.child.isShown()) {
		if (tr.hasClass('loading')) {
			// TODO: abort ajax
		}
		else {
			tr.removeClass('expanded');
			var innerDiv = tr.next().children(':first').children(':first');
			innerDiv.slideUp(400, function(event) {
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

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
	$('abbr.timeago').timeago();
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
	// Default settings
	$.extend($.fn.dataTable.defaults, {
		"dom": 'frtpli',
		"deferRender": true,
		"lengthMenu": [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
		"displayLength": 15,
		"drawCallback": function(settings) {
			groupByColumn(this);
		}
	});
}

function multiSelect() {
	if ($(this).hasClass('group')) {
		if ($(this).nextUntil('.group').not('.selected').length !== 0) {
			if (!shiftPressed) {
				$(this).siblings('.selected').removeClass('selected');
			}
			$(this).nextUntil('.group').addClass('selected');
		}
		else {
			$(this).nextUntil('.group').removeClass('selected');
		}
	}
	else {
		$(this).toggleClass('selected');
		if (shiftPressed) {
			var selected = $(this).hasClass('selected');
			if ($(this).prevAll('.selected').not('.group').length !== 0) {
				$(this).prevUntil('.selected').not('.group').each(function() {
					$(this).toggleClass('selected', selected);
				});
			}
			else if ($(this).nextAll('.selected').not('.group').length !== 0) {
				$(this).nextUntil('.selected').not('.group').each(function() {
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
function groupByColumn(dataTable) {
	var api = dataTable.api();
	var rows = api.rows({page: 'current'}).nodes();
	var last = null;
	var groupByColumn = getGroupByColumn(dataTable.selector);
	if (ctrlPressed) {
		// Dynamic group by column
		var primaryOrder = api.order()[0];
		if (groupByColumn === false || groupByColumn !== primaryOrder[0]) {
			groupByColumn = primaryOrder[0];
		}
		else if (groupByColumn === primaryOrder[0]) {
			groupByColumn = false;
		}
		setGroupByColumn(dataTable.selector, groupByColumn);
	}
	if (groupByColumn === false) {
		return;
	}
	api.column(groupByColumn, {page: 'current'}).data().each(function(group, i) {
		if (last !== group) {
			$(rows).eq(i).before('<tr class="group"><td colspan="7">' + group + '</td></tr>');
			last = group;
		}
	});
}
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
	// Default settings
	$.extend($.fn.dataTable.defaults, {
		"dom": 'frtpli',
		"deferRender": true,
		"lengthMenu": [[10, 15, 25, 50, 100, -1], [10, 15, 25, 50, 100, "Alles"]],
		"displayLength": 15,
		"drawCallback": function(settings) {
			groupByColumn(this, settings);
		}
	});
	// Custom filter
	$.fn.dataTable.ext.search.push(
			function(settings, data, index) {
				// TODO
				return true;
			}
	);
}

function childRow(td, dataTable) {
	var tr = td.closest('tr');
	var row = dataTable.row(tr);
	console.log(row);
	if (row.child.isShown()) {
		if (tr.hasClass('loading')) {
			// TODO: abort ajax
		}
		else {
			row.child.hide();
			tr.removeClass('childrow-shown');
		}
	}
	else {
		row.child('').show();
		tr.addClass('childrow-shown loading');
		var childtd = tr.next().addClass('childrow').children(':first');
		$.ajax({
			url: td.attr('href')
		}).done(function(data) {
			if (row.child.isShown()) {
				tr.removeClass('loading');
				childtd.html(data);
			}
		});
	}
}

function multiSelect(row) {
	if (row.hasClass('group')) {
		if (row.nextUntil('.group').not('.selected').length !== 0) {
			if (!shiftPressed) {
				row.siblings('.selected').removeClass('selected');
			}
			row.nextUntil('.group').addClass('selected');
		}
		else {
			row.nextUntil('.group').removeClass('selected');
		}
	}
	else {
		row.toggleClass('selected');
		if (shiftPressed) {
			var selected = row.hasClass('selected');
			if (row.prevAll('.selected').not('.group').length !== 0) {
				row.prevUntil('.selected').not('.group').each(function() {
					$(this).toggleClass('selected', selected);
				});
			}
			else if (row.nextAll('.selected').not('.group').length !== 0) {
				row.nextUntil('.selected').not('.group').each(function() {
					$(this).toggleClass('selected', selected);
				});
			}
		}
	}
}

function groupByColumn(dataTable, settings) {
	var api = dataTable.api();
	var groupByColumn = parseInt($(dataTable.selector).attr('groupByColumn'));
	if (isNaN(groupByColumn)) {
		groupByColumn = false;
	}
	if (ctrlPressed) {
		api.column(groupByColumn).visible(true);
		groupByColumn = api.order()[0][0];
		$(dataTable.selector).attr('groupByColumn', groupByColumn);
	}
	else if (!shiftPressed) {
		api.column(groupByColumn).visible(true);
		groupByColumn = false;
	}
	if (groupByColumn === false) {
		return;
	}
	api.column(groupByColumn).visible(false);
	// Create group rows
	var rows = api.rows({page: 'current'}).nodes();
	var last = null;
	api.column(groupByColumn, {page: 'current'}).data().each(function(group, i) {
		if (last !== group) {
			$(rows).eq(i).before('<tr class="group"><td colspan="' + settings.aoColumns.length + '">' + group + '</td></tr>');
			last = group;
		}
	});
}

/*! ColumnGroup 1.0.0
 */

/**
 * @summary     ColumnGroup
 * @description Provide the ability to group by a column in a DataTable
 * @version     1.0.0
 * @file        dataTables.columnGroup.js
 * @author      G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com
 * @author      P.W.G. Brussee <brussee@live.nl>
 *
 * Alternative implementation of the RowGroup extension for DataTables.
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery', 'datatables.net'], function ($) {
			return factory($, window, document);
		});
	}
	else if (typeof exports === 'object') {
		// CommonJS
		module.exports = function (root, $) {
			if (!root) {
				root = window;
			}

			if (!$ || !$.fn.dataTable) {
				$ = require('datatables.net')(root, $).$;
			}

			return factory($, root, root.document);
		};
	}
	else {
		// Browser
		factory(jQuery, window, document);
	}
}(function ($, window, document, undefined) {
	'use strict';
	var DataTable = $.fn.dataTable;

	/**
	 * ColumnGroup allows grouping by column for DataTables
	 * @class ColumnGroup
	 * @constructor
	 * @param {object} settings DataTables settings object
	 * @param {object} config ColumnGroup options
	 */
	var ColumnGroup = function (settings, config) {
		// Sanity check - you just know it will happen
		if (!(this instanceof ColumnGroup)) {
			throw "ColumnGroup must be initialised with the 'new' keyword.";
		}

		var dt = new DataTable.Api(settings);

		this.c = $.extend(true, {}, ColumnGroup.defaults, config);

		this.s = {
			dt: dt,
			collapsedGroups: [],
			regrouping: false,
			lastDraw: null
		};

		var dtSettings = dt.settings()[0];
		if (dtSettings._columnGroup) {
			throw "ColumnGroup already initialized on table " + dtSettings.nTable.id;
		}

		dtSettings._fixedHeader = this;

		this._fnConstruct();
	};

	$.extend(ColumnGroup.prototype, {
		/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 * Private methods (they are of course public in JS, but recommended as private)
		 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
		"_fnConstruct": function () {
			var dt = this.s.dt,
				table = dt.table(),
				tableNode = $(table.node()),
				that = this;

			DataTable.ext.search.push(function (settings, data, index) {
				return that._fnGroupExpandCollapseDraw(settings, data, index)
			});

			// Group by column
			tableNode.find('tbody').on('click', 'tr.group', function (event) {
				if (!event.shiftKey && !event.ctrlKey) {
					that._fnGroupExpandCollapse($(this));
				}
			});
			tableNode.find('thead').on('click', 'th.toggle-group:first', function () {
				that._fnGroupExpandCollapseAll($(this));
			});
			tableNode.on('draw.dt', function (event, settings) {
				that._fnGroupByColumnDraw(event, settings);
			});
			tableNode.find('thead tr th').first().addClass('toggle-group toggle-group-expanded');
		},

		"_fnGroupByColumnDraw": function (event, settings) {
			var dt = this.s.dt,
				table = dt.table(),
				tableNode = $(table.node());

			var collapsedGroups = this.s.collapsedGroups,
				lastDraw = this.s.lastDraw;

			var column = this.c.column;

				if (lastDraw === Date.now()) {
				return; // workaround childrow
			}

			var collapse = collapsedGroups.slice(); // copy by value
			var colspan = '';
			var j = tableNode.find('thead tr th').length - 2;
			for (var i = 0; i < j; i++) {
				colspan += '<td></td>';
			}
			var groupRow;
			// Create group rows for visible rows
			var rows = $(table.rows({page: 'current'}).nodes());
			tableNode.find('tr.group').remove();
			var last;
			// Iterate over data in the group by column
			table.column(column, {page: 'current'}).data().each(function (group, i) {
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
			// Create group rows for collapsed groups
			var tbody = tableNode.children('tbody:first');
			collapse.forEach(function (group) {
				groupRow = $('<tr class="group"><td class="toggle-group"></td><td class="group-label">' + group + '</td>' + colspan + '</tr>').data('groupData', group);
				tbody.append(groupRow);
			});
			this.s.lastDraw = Date.now();
		},

		"_fnGroupExpandCollapse": function ($tr) {
			var dt = this.s.dt,
				table = dt.table(),
				tableNode = $(table.node());

			var collapsedGroups = this.s.collapsedGroups;
			var td = $('td:first', $tr);
			td.toggleClass('toggle-group-expanded');
			var group = $tr.data('groupData');
			if (td.hasClass('toggle-group-expanded')) {
				collapsedGroups = $.grep(collapsedGroups, function (value) {
					return value !== group;
				});
			}
			else {
				collapsedGroups.push(group);
			}
			this.s.collapsedGroups = collapsedGroups.sort();
			dt.draw(false);
			this._fnHideEmptyCollapsedAll(tableNode.find('thead tr th:first'));
		},

		"_fnHideEmptyCollapsedAll": function ($th) {
			var dt = this.s.dt,
				table = dt.table(),
				tableNode = $(table.node());

			var collapsedGroups = this.s.collapsedGroups;

			if (tableNode.find('tr.group').length === collapsedGroups.length) {
				tableNode.find('td.dataTables_empty').parent().remove();
				$th.removeClass('toggle-group-expanded');
			} else {
				$th.addClass('toggle-group-expanded');
			}
		},

		"_fnGroupExpandCollapseAll": function ($th) {
			var dt = this.s.dt;

			var column = this.c.column;
			var collapsedGroups = [];

			if ($th.hasClass('toggle-group-expanded')) {
				var last;
				dt.column(column).data().each(function (group) {
					if (last !== group) {
						collapsedGroups.push(group);
						last = group;
					}
				});
			}
			this.s.collapsedGroups = collapsedGroups;
			dt.draw(false);
			this._fnHideEmptyCollapsedAll($th);
		},

		"_fnGroupExpandCollapseDraw": function (settings, data) {
			var column = this.c.column,
				collapsedGroups = this.s.collapsedGroups;

			var group = data[column];

			return $.inArray(group, collapsedGroups) <= -1;
		}
	});

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Static parameters
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	ColumnGroup.defaults = {
		column: null
	};

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Constants
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * ColumnGroup version
	 *  @constant  version
	 *  @type      String
	 *  @default   As code
	 */
	ColumnGroup.version = "1.0.0";

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * DataTables interfaces
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

// Expose
	$.fn.dataTable.ColumnGroup = ColumnGroup;
	$.fn.DataTable.ColumnGroup = ColumnGroup;

// Attach a listener to the document which listens for DataTables initialisation
// events so we can automatically initialise
	$(document).on('preInit.dt.columnGroup', function (e, settings) {
		if (e.namespace !== 'dt') {
			return;
		}

		var init = settings.oInit.columnGroup;
		var defaults = DataTable.defaults.columnGroup;

		if (init || defaults) {
			var opts = $.extend({}, init, defaults);

			if (init !== false) {
				new ColumnGroup(settings, opts);
			}
		}
	});

	return ColumnGroup;
}));

/*! ChildRow 1.0.0
 */

/**
 * @summary     ChildRow
 * @description Load an external data source for a child row.
 * @version     1.0.0
 * @file        dataTables.childRow.js
 * @author      G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @author      P.W.G. Brussee <brussee@live.nl>
 */

(function( factory ){
	if ( typeof define === 'function' && define.amd ) {
		// AMD
		define( ['jquery', 'datatables.net'], function ( $ ) {
			return factory( $, window, document );
		} );
	}
	else if ( typeof exports === 'object' ) {
		// CommonJS
		module.exports = function (root, $) {
			if ( ! root ) {
				root = window;
			}

			if ( ! $ || ! $.fn.dataTable ) {
				$ = require('datatables.net')(root, $).$;
			}

			return factory( $, root, root.document );
		};
	}
	else {
		// Browser
		factory( jQuery, window, document );
	}
}(function( $, window, document, undefined ) {
	'use strict';
	var DataTable = $.fn.dataTable;

	var ChildRow = function ( dt, config ) {
		// Sanity check - you just know it will happen
		if ( ! (this instanceof ChildRow) ) {
			throw "ChildRow must be initialised with the 'new' keyword.";
		}

		// Allow a boolean true for defaults
		if ( config === true ) {
			config = {};
		}

		dt = new DataTable.Api( dt );

		this.c = $.extend( true, {}, ChildRow.defaults, config );

		this.s = {
			dt: dt
		};

		var dtSettings = dt.settings()[0];
		if ( dtSettings._childRow ) {
			throw "ChildRow already initialised on table "+dtSettings.nTable.id;
		}

		dtSettings._childRow = this;

		this._fnConstructor();
	};


	/*
	 * Variable: ChildRow
	 * Purpose:  Prototype for ChildRow
	 * Scope:    global
	 */
	$.extend( ChildRow.prototype, {
		/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 * API methods
		 */

		fnToggleChildRow: function (td) {
			var dt = this.s.dt,
				table = dt.table();

			var tr = td.closest('tr');
			var row = dt.row(tr);
			var innerDiv;
			if (row.child.isShown()) {
				if (tr.hasClass('loading')) {
					// TODO: abort ajax
				} else {
					innerDiv = tr.next().children(':first').children(':first');
					innerDiv.slideUp(400, function () {
						row.child.hide();
						tr.removeClass('expanded');
					});
				}
			} else {
				row.child('<div class="innerDetails verborgen"></div>').show();
				tr.addClass('expanded loading');
				innerDiv = tr.next().addClass('childrow').children(':first').children(':first');
				var jqXHR = $.ajax({
					url: td.data('detailSource')
				});
				jqXHR.done(function (data, textStatus, jqXHR) {
					if (row.child.isShown()) {
						tr.removeClass('loading');
						innerDiv.html(data).slideDown();
					}
				});
				jqXHR.fail(function (jqXHR, textStatus, errorThrown) {
					if (row.child.isShown()) {
						tr.removeClass('loading');
						tr.find('td.toggle-childrow').html('<img title="' + errorThrown + '" src="/plaetjes/famfamfam/cancel.png" />');
					}
				});
			}
		},


		/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 * Constructor
		 */

		/**
		 * ChildRow constructor - adding the required event listeners and
		 * simple initialisation
		 *
		 * @private
		 */
		_fnConstructor: function () {
			var dt = this.s.dt,
				table = dt.table(),
				tableNode = $(table.node()),
				that = this;

			DataTable.ext.internal._fnCallbackReg(
				dt.settings()[0],
				'aoRowCreatedCallback',
				this._fnCreatedRowCallback,
				'child-row'
			);

			tableNode.find('tbody').on('click', 'tr td.toggle-childrow', function (event) {
				that.fnToggleChildRow($(this));
			});
		},


		/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 * Private methods
		 */

		/**
		 * Add a toggle button if needed.
		 *
		 * @param tr
		 * @param data
		 * @private
		 * @static
		 */
		_fnCreatedRowCallback: function(tr, data) {
			// Details from external source
			if ('detailSource' in data) {
				$(tr).children('td:first').addClass('toggle-childrow').data('detailSource', data.detailSource);
			}
		}
	} );


	/**
	 * Version
	 * @type {String}
	 * @static
	 */
	ChildRow.version = "1.0.0";

	/**
	 * Defaults
	 * @type {Object}
	 * @static
	 */
	ChildRow.defaults = {};


	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * DataTables interfaces
	 */

// Attach for constructor access
	$.fn.dataTable.ChildRow = ChildRow;
	$.fn.DataTable.ChildRow = ChildRow;


// DataTables creation - also create a ChildRow instance.
	$(document).on( 'preInit.dt.childRow', function (e, settings, json) {
		if ( e.namespace !== 'dt' ) {
			return;
		}

		var init = settings.oInit.childRow;
		var defaults = DataTable.defaults.childRow;

		if ( ! settings._childRow ) {
			var opts = $.extend( {}, defaults, init );

			if ( init !== false ) {
				new ChildRow( settings, opts );
			}
		}
	} );

	DataTable.Api.register( 'childRow.toggle()', function (td) {
		return this.iterator( 'table', function ( ctx ) {
			var fh = ctx._childRow;

			if ( fh ) {
				fh.fnToggleChildRow(td);
			}
		} );
	} );

	return ChildRow;
}));

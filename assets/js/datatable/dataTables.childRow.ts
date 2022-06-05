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

import $ from 'jquery';

class ChildRow {
	/**
	 * Version
	 * @type {String}
	 * @static
	 */
	public static version = '1.0.0';

	/**
	 * Add a toggle button if needed.
	 *
	 * @param tr
	 * @param data
	 * @private
	 * @static
	 */
	public static _fnCreatedRowCallback(
		tr: HTMLTableRowElement,
		data: { detailSource: string }
	) {
		// Details from external source
		if ('detailSource' in data) {
			$(tr)
				.children('td:first')
				.addClass('toggle-childrow')
				.data('detailSource', data.detailSource);
		}
	}

	private api: DataTables.Api;

	constructor(dt: string) {
		// Sanity check - you just know it will happen
		if (!(this instanceof ChildRow)) {
			throw new Error("ChildRow must be initialised with the 'new' keyword.");
		}

		this.api = new $.fn.dataTable.Api(dt);

		const dtSettings = this.api.settings()[0];
		if (dtSettings._childRow) {
			throw new Error(
				'ChildRow already initialised on table ' + dtSettings.nTable.id
			);
		}

		dtSettings._childRow = this;

		const tableNode = $(this.api.table(0).node());

		// eslint-disable-next-line @typescript-eslint/ban-ts-comment
		// @ts-ignore
		$.fn.dataTable.ext.internal._fnCallbackReg(
			this.api.settings()[0],
			'aoRowCreatedCallback',
			ChildRow._fnCreatedRowCallback,
			'child-row'
		);

		tableNode.find('tbody').on('click', 'tr td.toggle-childrow', (event) => {
			this.fnToggleChildRow($(event.target));
		});
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * API methods
	 */

	public fnToggleChildRow(td: JQuery<HTMLTableCellElement>) {
		const table = this.api.table(0);

		const tr = td.closest('tr');
		const row = this.api.row(tr);
		let innerDiv: JQuery;
		if (row.child.isShown()) {
			if (tr.hasClass('loading')) {
				// TODO: abort ajax
			} else {
				innerDiv = tr.next().children(':first').children(':first');
				innerDiv.slideUp(400, () => {
					row.child.hide();
					tr.removeClass('expanded');
				});
			}
		} else {
			row.child('<div class="innerDetails verborgen"></div>').show();
			tr.addClass('expanded loading');
			innerDiv = tr
				.next()
				.addClass('childrow')
				.children(':first')
				.children(':first');

			$.ajax({
				url: td.data('detailSource'),
			})
				.done((data) => {
					if (row.child.isShown()) {
						tr.removeClass('loading');
						innerDiv.html(data).slideDown();
						$(table.node()).trigger('childRow.dt', { container: innerDiv });
					}
				})
				.fail((_, textStatus, errorThrown) => {
					if (row.child.isShown()) {
						tr.removeClass('loading');
						tr.find('td.toggle-childrow').html(
							`<img title="${errorThrown}" alt="cancel" src="/plaetjes/famfamfam/cancel.png" />`
						);
					}
				});
		}
	}
}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * DataTables interfaces
 */

declare global {
	// eslint-disable-next-line @typescript-eslint/no-namespace
	namespace DataTables {
		// noinspection JSUnusedGlobalSymbols
		interface StaticFunctions {
			ChildRow: typeof ChildRow;
		}
	}
}

// Attach for constructor access
$.fn.dataTable.ChildRow = ChildRow;
// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
$.fn.DataTable.ChildRow = ChildRow;

// DataTables creation - also create a ChildRow instance.
$(document).on('preInit.dt.childRow', (e, settings) => {
	if (e.namespace !== 'dt') {
		return;
	}

	const init = settings.oInit.childRow;

	if (!settings._childRow) {
		if (init !== false) {
			new ChildRow(settings);
		}
	}
});

// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
$.fn.dataTable.Api.register(
	'childRow.toggle()',
	function (this: DataTables.TablesMethods, td: JQuery<HTMLTableCellElement>) {
		// eslint-disable-next-line @typescript-eslint/ban-ts-comment
		// @ts-ignore
		return this.iterator('table', (ctx: { _childRow: ChildRow }) => {
			const fh = ctx._childRow;

			if (fh) {
				fh.fnToggleChildRow(td);
			}
		});
	}
);

/*!
 * csrdelft.dataTables.js
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Group by & multi-select capabilities.
 */

$(document).ready(function () {
	fnInitDataTables();
});


function fnInitDataTables() {
    // Verwerk een multipliciteit in de vorm van `== 1` of `!= 0` of `> 3` voor de selecties
	// Returns bool
    var evaluateMultiplicity = function (expression, num) {
    	// Altijd laten zien bij geen expressie
    	if (expression.length === 0) return true;
        var operator_num = expression.split(' ');
        var expression_operator = operator_num[0];
        var expression_num = parseInt(operator_num[1]);
		var operatorToFunction = {
			'==': function (a, b) { return a === b; },
			'!=': function (a, b) { return a !== b; },
			'>=': function (a, b) { return a >= b; },
			'>' : function (a, b) { return a >  b; },
			'<=': function (a, b) { return a <= b; },
			'<' : function (a, b) { return a <  b; }
		};

		return operatorToFunction[expression_operator](num, expression_num);
    };

    // Zet de icons van de default buttons
    $.fn.dataTable.ext.buttons.copyHtml5.className += ' dt-button-ico dt-ico-page_white_copy';
	$.fn.dataTable.ext.buttons.copyFlash.className += ' dt-button-ico dt-ico-page_white_copy';
	$.fn.dataTable.ext.buttons.csvHtml5.className += ' dt-button-ico dt-ico-page_white_text';
	$.fn.dataTable.ext.buttons.csvFlash.className += ' dt-button-ico dt-ico-page_white_text';
	$.fn.dataTable.ext.buttons.pdfHtml5.className += ' dt-button-ico dt-ico-page_white_acrobat';
	$.fn.dataTable.ext.buttons.pdfFlash.className += ' dt-button-ico dt-ico-page_white_acrobat';
	$.fn.dataTable.ext.buttons.excelHtml5.className += ' dt-button-ico dt-ico-page_white_excel';
	$.fn.dataTable.ext.buttons.excelFlash.className += ' dt-button-ico dt-ico-page_white_excel';
    $.fn.dataTable.ext.buttons.print.className += ' dt-button-ico dt-ico-printer';

    // Laat een modal zien, of doe een ajax call gebasseerd op selectie.
    $.fn.dataTable.ext.buttons.default = {
        init: function (dt, node, config) {
            var that = this;
            var toggle = function () {
				that.enable(
					evaluateMultiplicity(
						config.multiplicity,
						dt.rows({selected: true}).count()
					)
				);
			};
            dt.on('select.dt.DT deselect.dt.DT', toggle);
            // Initiele staat
            toggle();

            // Vervang :col door de waarde te vinden in de geselecteerde row
			// Dit wordt alleen geprobeerd als dit voorkomt
            if (config.href.indexOf(':') !== -1) {
            	var replacements = /:(\w+)/g.exec(config.href);
            	dt.on('select.dt.DT', function (e, dt, type, indexes) {
            		if (indexes.length === 1) {
						var newHref = config.href;
            			var row = dt.row(indexes).data();
            			// skipt match, start met groepen
						for (var i = 1; i < replacements.length; i++) {
							newHref = newHref.replace(':' + replacements[i], row[replacements[i]]);
						}

						node.attr('href', newHref)
					}
				})
			}

            // Settings voor knop_ajax
            node.attr('href', config.href);
            node.attr('data-tableid', dt.context[0].sTableId);
        },
        action: function( e, dt, button, config) {
            knop_post.call(button, e)
        },
        className: 'post DataTableResponse'
    };

    $.fn.dataTable.ext.buttons.popup = {
    	extend: 'default',
    	action: function(e, dt, button, config) {
    		window.open(button.attr('href'));
		}
	};

    $.fn.dataTable.ext.buttons.url = {
    	extend: 'default',
		action: function(e, dt, button, config) {
    		window.location.href = button.attr('href');
		}
	};

    // Verander de bron van een datatable
	// De knop is ingedrukt als de bron van de datatable
	// gelijk is aan de bron van de knop.
	$.fn.dataTable.ext.buttons.sourceChange = {
		init: function (dt, node, config) {
			var enable = function () {
				dt.buttons(node).active(dt.ajax.url() === config.href);
			};
			dt.on('xhr.sourceChange', enable);

			enable();
		},
		action: function (e, dt, button, config) {
			dt.ajax.url(config.href).load();
		}
	};

	$.fn.dataTable.ext.buttons.confirm = {
		extend: 'collection',
		init: function (dt, node, config) {
			var that = this;
			var toggle = function () {
				that.enable(
					evaluateMultiplicity(
						config.multiplicity,
						dt.rows({selected: true}).count()
					)
				);
			};
			dt.on('select.dt.DT deselect.dt.DT', toggle);
			// Initiele staat
			toggle();

			var action = config.action;

			var buttons = new $.fn.dataTable.Buttons( dt, {
				buttons: [
					{
						extend: 'default',
						text: function ( dt ) {
							return dt.i18n( 'csr.zeker', 'Are you sure?' );
						},
						action: action,
						multiplicity: '', // altijd mogelijk
						className: 'dt-button-ico dt-ico-exclamation dt-button-warning',
						href: config.href
					}
				]
			} );

			config._collection.append(buttons.dom.container.children());

			// Reset action to extend one.
			config.action = $.fn.dataTable.ext.buttons.collection.action;
		},
		action: function( e, dt, button, config ) {
			knop_post.call(button, e)
		}
	};

	$.fn.dataTable.ext.buttons.defaultCollection = {
		extend: 'collection',
		init: function (dt, node, config) {
			$.fn.dataTable.ext.buttons.default.init.call(this, dt, node, config);
		}
	};

	$('body').on('click', function () {
		// Verwijder tooltips als de datatable modal wordt gesloten
		$(".ui-tooltip-content").parents('div').remove();
	})
}

function fnAutoScroll(tableId) {
	var $table = $(tableId);
	var $scroll = $table.parent();
	if ($scroll.hasClass('dataTables_scrollBody')) {
		// autoscroll if already on bottom
		if ($scroll.scrollTop() + $scroll.innerHeight() >= $scroll[0].scrollHeight - 20) {
			// check before draw and scroll after
			window.setTimeout(function () {
				$scroll.animate({
					scrollTop: $scroll[0].scrollHeight
				}, 800);
			}, 200);
		}
	}
}

function fnUpdateDataTable(tableId, response) {
	var $table = $(tableId);
	var table = $table.DataTable();
	// update or remove existing rows or add new rows
	response.data.forEach(function (row) {
		var $tr = $('tr[data-uuid="' + row.UUID + '"]');
		if ($tr.length === 1) {
			if ('remove'in row) {
				table.row($tr).remove();
			}
			else {
				table.row($tr).data(row);
				init_context($tr);
			}
		}
		else if ($tr.length === 0) {
			table.row.add(row);
		}
		else {
			alert($tr.length);
		}
	});
	table.draw(false);
}

function fnGetSelection(tableId) {
	var selection = [];
	$(tableId + ' tbody tr.selected').each(function () {
		selection.push($(this).attr('data-uuid'));
	});
	return selection;
}

function fnChildRow(tableId, $td, column) {
	var tr = $td.closest('tr');
	var row = $(tableId).DataTable().row(tr);
	if (row.child.isShown()) {
		if (tr.hasClass('loading')) {
			// TODO: abort ajax
		}
		else {
			var innerDiv = tr.next().children(':first').children(':first');
			innerDiv.slideUp(400, function () {
				row.child.hide();
				tr.removeClass('expanded');
			});
		}
	}
	else {
		row.child('<div class="innerDetails verborgen"></div>').show();
		tr.addClass('expanded loading');
		var innerDiv = tr.next().addClass('childrow').children(':first').children(':first');
		var jqXHR = $.ajax({
			url: $td.data('detailSource')
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
}

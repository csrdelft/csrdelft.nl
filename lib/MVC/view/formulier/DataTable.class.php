<?php
require_once 'MVC/view/formulier/TabsForm.class.php';

/**
 * DataTable.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * DataTables plug-in for jQuery
 * 
 * @see http://www.datatables.net/
 * 
 */
class DataTable extends TabsForm {

	protected $tableId;
	protected $dataUrl;
	private $groupByColumn;
	private $groupByLocked = false;
	protected $defaultLength = -1;
	private $columns = array();
	protected $settings = array(
		'dom'		 => 'Tfrtpli',
		'tableTools' => array(
			'sRowSelect' => 'os',
			'aButtons'	 => array(
				'print',
				'pdf',
				'xls',
				'select_all',
				'select_none',
				'copy'
			),
			'sSwfPath'	 => '/layout/js/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf'
		),
		'lengthMenu' => array(
			array(10, 25, 50, 100, -1),
			array(10, 25, 50, 100, 'Alles')
		),
		'language'	 => array(
			'sProcessing'		 => 'Bezig...',
			'sLengthMenu'		 => '_MENU_ resultaten weergeven',
			'sZeroRecords'		 => 'Geen resultaten gevonden',
			'sInfo'				 => '_START_ tot _END_ van _TOTAL_ resultaten',
			'sInfoEmpty'		 => 'Geen resultaten om weer te geven',
			'sInfoFiltered'		 => ' (gefilterd uit _MAX_ resultaten)',
			'sInfoPostFix'		 => '',
			'sSearch'			 => '',
			'sEmptyTable'		 => 'Geen resultaten aanwezig in de tabel',
			'sInfoThousands'	 => '.',
			'sLoadingRecords'	 => 'Een moment geduld aub - bezig met laden...',
			'oPaginate'			 => array(
				'sFirst'	 => 'Eerste',
				'sLast'		 => 'Laatste',
				'sNext'		 => 'Volgende',
				'sPrevious'	 => 'Vorige'
			)
		)
	);

	public function __construct($class, $tableId, $titel = false, $groupByColumn = null) {
		parent::__construct(new $class(), $tableId . '_toolbar', null, $titel);

		$this->tableId = $tableId;
		$this->css_classes[] = 'display';
		if ($groupByColumn !== false) {
			$this->css_classes[] = 'groupByColumn';
		}
		$this->groupByColumn = $groupByColumn;

		// create group expand / collapse column
		$this->columns['details'] = array(
			'name'			 => 'details',
			'data'			 => null,
			'title'			 => '',
			'type'			 => 'string',
			'orderable'		 => false,
			'searchable'	 => false,
			'defaultContent' => ''
		);

		// generate columns from entity attributes
		foreach ($this->model->getAttributes() as $attribute) {
			$this->addColumn($attribute);
		}

		// hide primary key columns
		foreach ($this->model->getPrimaryKey() as $attribute) {
			$this->hideColumn($attribute);
		}
	}

	protected function addKnop(DataTableKnop $knop, $tab = 'head') {
		$this->addFields(array($knop), $tab);
	}

	protected function addColumn($newName, $before = null) {
		// column definition
		$newColumn = array(
			'name'		 => $newName,
			'data'		 => $newName,
			'title'		 => ucfirst(str_replace('_', ' ', $newName)),
			'type'		 => 'string',
			'searchable' => false
				/*
				  //TODO: sort by other column
				  { "iDataSort": 1 },
				  reldate(getDateTime());

				  //TODO: custom rendering
				  /*
				  // The `data` parameter refers to the data for the cell (defined by the
				  // `data` option, which defaults to the column being worked with, in
				  // this case `data: 0`.
				  "render": function ( data, type, row ) {
				  return data +' ('+ row[3]+')';
				  }
				 */
		);
		// append or insert at position
		if ($before === null) {
			$this->columns[$newName] = $newColumn;
		} else {
			$array = array();
			foreach ($this->columns as $name => $column) {
				if ($name == $before) {
					$array[$newName] = $newColumn;
				}
				$array[$name] = $column;
			}
			$this->columns = $array;
		}
	}

	protected function hideColumn($name, $visible = false) {
		$this->columns[$name]['visible'] = (bool) $visible;
	}

	protected function searchColumn($name, $searchable = true) {
		$this->columns[$name]['searchable'] = (bool) $searchable;
	}

	protected function getSettings() {

		// set view modus: paging or scrolling
		if ($this->defaultLength > 0) {
			$this->settings['iDisplayLength'] = $this->defaultLength;
			$this->settings['paging'] = true;
		} else {
			$this->settings['paging'] = false;
			//$settings['scrollX'] = '100%';
			//$settings['scrollY'] = '100%';
		}

		// set ajax url
		if ($this->dataUrl) {
			$this->settings['ajax'] = array(
				'url'		 => $this->dataUrl,
				'type'		 => 'POST',
				'data'		 => 'lastUpdate',
				'dataSrc'	 => 'fnAjaxUpdateCallback'
			);
		}
		$this->settings['createdRow'] = 'fnCreatedRowCallback';

		// get columns index
		$columns = array_keys($this->columns);

		// group by column
		if (isset($this->columns[$this->groupByColumn])) {

			// make group by column invisible and searchable
			$this->hideColumn($this->groupByColumn);
			$this->searchColumn($this->groupByColumn);

			$this->groupByColumn = array_search($this->groupByColumn, $columns);
			$this->settings['orderFixed'] = array(
				array($this->groupByColumn, 'asc')
			);
		}

		// create visible columns index array and default order
		$index = 0;
		$visibleIndex = 0;
		foreach ($this->columns as $name => $def) {
			if (!isset($def['visible']) OR $def['visible'] === true) {

				// default order by first visible orderable column
				if (!isset($this->settings['order']) AND ! (isset($def['orderable']) AND $def['orderable'] === false)) {
					$this->settings['order'] = array(
						array($index, 'asc')
					);
				}

				$visibleIndex++;
			}
			$index++;
		}

		// translate columns index
		$this->settings['columns'] = array_values($this->columns);

		return $this->settings;
	}

	public function view() {
		// encode settings
		$settingsJson = json_encode($this->getSettings(), DEBUG ? JSON_PRETTY_PRINT : 0);

		// js function calls
		$settingsJson = str_replace('"lastUpdate"', '{"lastUpdate":lastUpdate' . $this->tableId . '}', $settingsJson);
		$settingsJson = str_replace('"fnAjaxUpdateCallback"', 'fnAjaxUpdateCallback', $settingsJson);
		$settingsJson = str_replace('"fnCreatedRowCallback"', 'fnCreatedRowCallback', $settingsJson);
		?>
		<?php parent::view(); ?>
		<table id="<?= $this->tableId ?>" class="<?= implode(' ', $this->css_classes) ?>" groupbycolumn="<?= $this->groupByColumn ?>"></table>
		<script type="text/javascript">
			var lastUpdate<?= $this->tableId; ?>;

			$(document).ready(function () {

				var fnAutoUpdate = function () {
					var oTable = $('#<?= $this->tableId; ?>').DataTable();
					oTable.ajax.reload();
				};

				var fnAjaxUpdateCallback = function (json) {
					lastUpdate<?= $this->tableId; ?> = Math.round(new Date().getTime());
					//setTimeout(fnAutoUpdate, 5000);
					// TODO: remember focus position on update
					/*
					 var oTable = $('#example').dataTable();
					 var keys = new $.fn.dataTable.KeyTable( oTable );
					 keys.fnSetPosition( 1, 1 );
					 */
					init(); // FIXME
					updateToolbar();
					return json.data;
				};

				var fnCreatedRowCallback = function (tr, data, index) {
					$(tr).attr('data-objectid', data.objectId);
					if ('detailSource' in data) {
						$(tr).children('td:first').addClass('toggle-childrow').data('detailSource', data.detailSource);
					}
					try {
						$('abbr.timeago', tr).timeago();
					} catch (e) {
						// missing js
					}
					// voor elke td check of deze editable moet zijn 
					$(tr).children().each(function (columnIndex, td) {
						if ($(td).children(':first').hasClass('InlineForm')) {
							var edit = function (event) {
								var form = $(td).find('form');
								form.prev('.InlineFormToggle').hide();
								form.show();
								setTimeout(function () { // werkomheen focus keys plugin
									form.find('.FormElement:first').focus();
								}, 1);
							};
							$(td).addClass('editable').click(edit);
						}
					});
				};

				var tableId = '#<?= $this->tableId; ?>';
				var oTable = $(tableId).DataTable(<?= $settingsJson; ?>);
				var keys = new $.fn.dataTable.KeyTable($(tableId));

				// Toolbar update on row selection
				var updateToolbar = <?= $this->getUpdateToolbar(); ?>;
				// Multiple selection of group rows
				$(tableId + ' tbody').on('click', 'tr', function (event) {
					if (bShiftPressed || bCtrlPressed || !$(this).hasClass('group')) {
						fnMultiSelect(event, $(this));
					}
					updateToolbar();
				});

				$('.DTTT_button_text').on('click', updateToolbar);
				// Toolbar above table
				$(tableId + '_toolbar').prependTo(tableId + '_wrapper');
				$(tableId + '_toolbar h1.Titel').prependTo(tableId + '_wrapper');
				$('.DTTT_container').children().prependTo(tableId + '_toolbar');
				$('.DTTT_container').remove();
				// Toolbar table filter formatting
				$(tableId + '_filter input').attr('placeholder', 'Zoeken').unwrap();
				$(tableId + '_filter').appendTo(tableId + '_toolbar');
				// Opening and closing details
				$(tableId + ' tbody').on('click', 'tr td.toggle-childrow', function (event) {
					fnChildRow(oTable, $(this));
				});
				// Group by column
				$(tableId + '.groupByColumn tbody').on('click', 'tr.group', function (event) {
					if (!bShiftPressed && !bCtrlPressed) {
						fnGroupExpandCollapse(oTable, $(tableId), $(this));
					}
				});
				$(tableId + '.groupByColumn thead').on('click', 'th.toggle-group:first', function (event) {
					fnGroupExpandCollapseAll(oTable, $(tableId), $(this));
				});
		<?php if (!$this->groupByLocked): ?>
					$(tableId + '.groupByColumn').on('order.dt', fnGroupByColumn);
		<?php endif; ?>
				$(tableId + '.groupByColumn').on('draw.dt', fnGroupByColumnDraw);
				$(tableId + '.groupByColumn').data('collapsedGroups', []);
				if ($(tableId).hasClass('groupByColumn') && fnGetGroupByColumn($(tableId))) {
					$(tableId + ' thead tr th').first().addClass('toggle-group toggle-group-expanded');
				}

				// Keyboard multiselect support with spacebar
				$(document).keyup(function (event) {
					// Geen keyboard shortcuts als we in een input-element of text-area zitten.
					var element = event.target.tagName.toUpperCase();
					if (element == 'INPUT' || element == 'TEXTAREA' || element == 'SELECT') {
						return;
					}
					if (keyshortcuts.indexOf(event.keyCode) >= 0) {
						event.preventDefault();
						var td = keys.fnGetCurrentTD();
						if (td) {
							fnMultiSelect(event, $(td).parent());
							updateToolbar();
							if (event.keyCode === 13 || event.keyCode === 32) {
								$(td).trigger('click');
							}
						}
					}
		<?php
		$keyshortcuts = '[32,13'; // space
		foreach ($this->getFields() as $field) {
			if ($field instanceof DataTableKnop AND is_int($field->keyshortcut)) {
				$keyshortcuts .= ',' . $field->keyshortcut;
				echo "if (event.keyCode === {$field->keyshortcut}) { return $('#{$field->getId()}').trigger('click'); }\n";
			}
		}
		$keyshortcuts .= ']';
		?>
				});
				var keyshortcuts = <?= $keyshortcuts; ?>;
				$(document).keydown(function (event) {
					// Geen keyboard shortcuts als we in een input-element of text-area zitten.
					var element = event.target.tagName.toUpperCase();
					if (element != 'INPUT' && element != 'TEXTAREA' && element != 'SELECT' && keyshortcuts.indexOf(event.keyCode) >= 0) {
						event.preventDefault();
					}
				});
			});
		</script>
		<?php
	}

	/**
	 * Update datatable knoppen based on selection (count).
	 * 
	 * @return javascript
	 */
	private function getUpdateToolbar() {
		$js = <<<JS
function () {
	var selectie = fnGetSelection(tableId);
	var aantal = selectie.length;
	console.log(selectie);
JS;
		foreach ($this->getFields() as $field) {
			if ($field instanceof DataTableKnop) {
				$js .= "\n" . $field->getUpdateToolbar() . "\n";
			}
		}
		return $js . '}';
	}

}

class DataTableKnop extends FormulierKnop {

	public $keyshortcut;
	private $multiplicity;

	public function __construct($multiplicity, $url, $action, $key, $label, $title, $css_class, $float_left = true) {
		parent::__construct($url, $action, $label, $title, null, $float_left);
		$this->multiplicity = $multiplicity;
		$this->keyshortcut = $key;
		$this->css_classes[] = 'DTTT_button';
		$this->css_classes[] = $css_class;
	}

	public function getUpdateToolbar() {
		return "$('#{$this->getId()}').attr('disabled', !(aantal {$this->multiplicity})).toggleClass('DTTT_disabled', !(aantal {$this->multiplicity}));";
	}

}

class DataTableResponse extends JsonResponse {

	protected $tableId;

	public function __construct($table, $data) {
		parent::__construct($data);
		$this->tableId = $table;
	}

	public function getJson($data) {
		return json_encode($data);
	}

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo '{"table":"#' . $this->tableId . '", "data":[' . "\n";
		$comma = false;
		foreach ($this->model as $data) {
			if ($comma) {
				echo ",\n";
			} else {
				$comma = true;
			}
			echo $this->getJson($data);
		}
		echo "\n]}";
	}

}

<?php
require_once 'view/formulier/TabsForm.class.php';

/**
 * DataTable.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Uses DataTables plug-in for jQuery.
 * @see http://www.datatables.net/
 * 
 */
class DataTable extends TabsForm {

	public $nestedForm = true;
	protected $tableId;
	protected $dataUrl;
	protected $autoUpdate = false;
	private $groupByColumn;
	private $groupByLocked = false;
	protected $defaultLength = 10;
	private $columns = array();
	protected $settings = array(
		'dom'		 => 'fTrtpli',
		'responsive' => true,
		'tableTools' => array(
			'sRowSelect' => 'os',
			'aButtons'	 => array(
				'select_all',
				'select_none',
				'copy',
				'xls',
				'pdf',
				'print'
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

	public function __construct($orm, $titel = false, $groupByColumn = null) {
		parent::__construct(new $orm(), null, $titel);
		$this->tableId = $this->formId;
		$this->formId .= '_toolbar';
		$this->css_classes[] = 'DataTableToolbar';
		$this->groupByColumn = $groupByColumn;

		// create group expand / collapse column
		$this->columns['details'] = array(
			'name'			 => 'details',
			'data'			 => 'details',
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

	public function getTableId() {
		return $this->tableId;
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

	protected function hideColumn($name, $hide = true) {
		if (isset($this->columns[$name])) {
			$this->columns[$name]['visible'] = $hide ? false : true;
		}
	}

	protected function searchColumn($name, $searchable = true) {
		if (isset($this->columns[$name])) {
			$this->columns[$name]['searchable'] = (boolean) $searchable;
		}
	}

	protected function setColumnTitle($name, $title) {
		if (isset($this->columns[$name])) {
			$this->columns[$name]['title'] = $title;
		}
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
		} else {
			$this->groupByColumn = false;
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

		// toolbar
		parent::view();
		?>
		<table id="<?= $this->tableId; ?>" class="display <?= ($this->groupByColumn !== false ? 'groupByColumn' : ''); ?>" groupbycolumn="<?= $this->groupByColumn; ?>"></table>
		<script type="text/javascript">
			var lastUpdate<?= $this->tableId; ?>;

			$(document).ready(function () {
				/**
				 * Called after row addition and row data update.
				 * 
				 * @param object tr
				 * @param objectdata
				 * @param int rowIndex
				 */
				var fnCreatedRowCallback = function (tr, data, rowIndex) {
					$(tr).attr('data-UUID', data.UUID);
					init_context(tr);
					// Details from external source
					if ('detailSource' in data) {
						$(tr).children('td:first').addClass('toggle-childrow').data('detailSource', data.detailSource);
					}
					$(tr).children().each(function (columnIndex, td) {
						// Init custom buttons in rows
						$(td).children('a.post').each(function (i, a) {
							$(a).attr('data-tableid', '<?= $this->tableId; ?>');
						});
						// Init InlineForms
						if ($(td).children(':first').hasClass('InlineForm')) {
							var edit = function (event) {
								var form = $(td).find('form');
								// Toggle show form
								form.prev('.InlineFormToggle').hide();
								form.show();
								setTimeout(function () { // Workaround focus stealing keys plugin
									form.find('.FormElement:first').focus();
								}, 1);
							};
							$(td).addClass('editable').click(edit);
						}
					});
				};
				/**
				 * Called after ajax load complete.
				 * 
				 * @param object json
				 * @returns object
				 */
				var fnAjaxUpdateCallback = function (json) {
					lastUpdate<?= $this->tableId; ?> = Math.round(new Date().getTime());
		<?php
		if ($this->autoUpdate > 0) {
			echo "setTimeout(fnAutoUpdate, {$this->autoUpdate});";
		}
		?>
					/* TODO: remember focus position on update
					 var oTable = $('#example').dataTable();
					 var keys = new $.fn.dataTable.KeyTable( oTable );
					 keys.fnSetPosition( 1, 1 );
					 */
					fnUpdateToolbar();
					return json.data;
				};
				// Init DataTable
				var tableId = '#<?= $this->tableId; ?>';
				var table = $(tableId).DataTable(<?= $settingsJson; ?>);
				//var keys = new $.fn.dataTable.KeyTable($(tableId));
				/**
				 * Reload table data.
				 */
				var fnAutoUpdate = function () {
					table.ajax.reload();
				};
				/**
				 * Toolbar button state update on row (de-)selection.
				 */
				var fnUpdateToolbar = <?= $this->getUpdateToolbarFunction(); ?>;
				$(tableId + ' tbody').on('click', 'tr', fnUpdateToolbar);
				$('.DTTT_button_text').on('click', fnUpdateToolbar); // (De-)Select all
				$(tableId + '_toolbar').prependTo(tableId + '_wrapper'); // Toolbar above table
				$(tableId + '_toolbar h1.Titel').prependTo(tableId + '_wrapper'); // Title above toolbar
				$('.DTTT_container').children().appendTo(tableId + '_toolbar'); // Buttons inside toolbar
				$('.DTTT_container').remove(); // Remove buttons container
				$(tableId + '_filter input').attr('placeholder', 'Zoeken').unwrap(); // Remove filter container
				$(tableId + '_filter').prependTo(tableId + '_toolbar'); // Filter inside toolbar
				// Toggle details childrow
				$(tableId + ' tbody').on('click', 'tr td.toggle-childrow', function (event) {
					fnChildRow(table, $(this));
				});
				// Group by column
				$(tableId + '.groupByColumn tbody').on('click', 'tr.group', function (event) {
					if (!bShiftPressed && !bCtrlPressed) {
						fnGroupExpandCollapse(table, $(tableId), $(this));
					}
				});
				$(tableId + '.groupByColumn thead').on('click', 'th.toggle-group:first', function (event) {
					fnGroupExpandCollapseAll(table, $(tableId), $(this));
				});
		<?php if (!$this->groupByLocked) { ?>
					$(tableId + '.groupByColumn').on('order.dt', fnGroupByColumn);
		<?php } ?>
				$(tableId + '.groupByColumn').on('draw.dt', fnGroupByColumnDraw);
				$(tableId + '.groupByColumn').data('collapsedGroups', []);
				if ($(tableId).hasClass('groupByColumn') && fnGetGroupByColumn($(tableId))) {
					$(tableId + ' thead tr th').first().addClass('toggle-group toggle-group-expanded');
				}
			});
		</script>
		<?php
	}

	/**
	 * Update datatable knoppen based on selection (count).
	 * 
	 * @return javascript
	 */
	private function getUpdateToolbarFunction() {
		$js = <<<JS
function () {
	var selectie = fnGetSelection(tableId);
	var aantal = selectie.length;
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

	private $multiplicity;
	protected $tableId;

	public function __construct($multiplicity, $tableId, $url, $action, $label, $title, $class) {
		parent::__construct($url, $action . ' DataTableResponse', $label, $title, null);
		$this->multiplicity = $multiplicity;
		$this->tableId = $tableId;
		$this->css_classes[] = 'DTTT_button DTTT_button_' . $class;
	}

	public function getUpdateToolbar() {
		return "$('#{$this->getId()}').attr('disabled', !(aantal {$this->multiplicity})).toggleClass('DTTT_disabled', !(aantal {$this->multiplicity}));";
	}

	public function getHtml() {
		return str_replace('<a ', '<a data-tableid="' . $this->tableId . '" ', parent::getHtml());
	}

}

class DataTableResponse extends JsonResponse {

	public function getJson($entity) {
		return json_encode($entity);
	}

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo '{"data":[' . "\n";
		$comma = false;
		foreach ($this->model as $entity) {
			if ($comma) {
				echo ",\n";
			} else {
				$comma = true;
			}
			echo $this->getJson($entity);
		}
		echo "\n]}";
	}

}

class RemoveRowsResponse extends DataTableResponse {

	public function getJson($entity) {
		return parent::getJson(array(
					'UUID'	 => $entity->getUUID(),
					'remove' => true
		));
	}

}

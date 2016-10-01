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
abstract class DataTable extends TabsForm {

	public $nestedForm = true;
	public $filter = null;
	protected $dataUrl;
	private $groupByColumn;
	private $groupByLocked = false;
	protected $defaultLength = 10;
	private $columns = array();
	protected $settings = array(
		'deferRender'	 => true,
		'dom'			 => 'fTrtpli',
		'tableTools'	 => array(
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
		'lengthMenu'	 => array(
			array(10, 25, 50, 100, -1),
			array(10, 25, 50, 100, 'Alles')
		),
		'language'		 => array(
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

	public function __construct($orm, $dataUrl, $titel = false, $groupByColumn = null) {
		parent::__construct(new $orm(), null, $titel, true);
		$this->dataUrl = $dataUrl;
		$this->dataTableId = $this->formId;
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

	protected function addKnop(DataTableKnop $knop, $tab = 'head') {
		$this->addFields(array($knop), $tab);
	}

	protected function addColumn($newName, $before = null, $defaultContent = null, $render = null) {
		// column definition
		$newColumn = array(
			'name'		 => $newName,
			'data'		 => $newName,
			'title'		 => ucfirst(str_replace('_', ' ', $newName)),
			'defaultContent' => $defaultContent,
			'type'		 => 'string',
			'searchable' => false,
			'render' => $render
				/*
				  //TODO: sort by other column
				  { "iDataSort": 1 },
				  reldate(getDateTime());
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
			$this->settings['paging'] = true;
			$this->settings['iDisplayLength'] = $this->defaultLength;
		} else {
			$this->settings['paging'] = false;
			$this->settings['dom'] = str_replace('i', '', $this->settings['dom']);
		}

		// set ajax url
		if ($this->dataUrl) {
			$this->settings['ajax'] = array(
				'url'		 => $this->dataUrl,
				'type'		 => 'POST',
				'data'		 => array(
					'lastUpdate' => 'fnGetLastUpdate'
				),
				'dataSrc'	 => 'fnAjaxUpdateCallback'
			);
		}
		$this->settings['createdRow'] = 'fnCreatedRowCallback';
		$this->settings['drawCallback'] = 'fnUpdateToolbar';

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

	public function view() {
		// encode settings
		$settingsJson = json_encode($this->getSettings(), DEBUG ? JSON_PRETTY_PRINT : 0);

		// js function calls
		$settingsJson = str_replace('"fnGetLastUpdate"', 'fnGetLastUpdate', $settingsJson);
		$settingsJson = str_replace('"fnAjaxUpdateCallback"', 'fnAjaxUpdateCallback', $settingsJson);
		$settingsJson = str_replace('"fnCreatedRowCallback"', 'fnCreatedRowCallback', $settingsJson);
        $settingsJson = str_replace('"fnUpdateToolbar"', 'fnUpdateToolbar', $settingsJson);
        $settingsJson = preg_replace('/"render":\s?"(.+)"/', '"render": $1', $settingsJson);

		// toolbar
		parent::view();
		?>
		<table id="<?= $this->dataTableId; ?>" class="display <?= ($this->groupByColumn !== false ? 'groupByColumn' : ''); ?>" groupbycolumn="<?= $this->groupByColumn; ?>"></table>
		<script type="text/javascript">
			<?= $this->getJavascript(); ?>

			$(document).ready(function () {

				var fnUpdateToolbar = <?= $this->getUpdateToolbarFunction(); ?>;
				var fnGetLastUpdate = function () {
					return Number($('#<?= $this->dataTableId; ?>').attr('data-lastupdate'));
				}
				var fnSetLastUpdate = function (lastUpdate) {
					$('#<?= $this->dataTableId; ?>').attr('data-lastupdate', lastUpdate);
				}
				/**
				 * Called after row addition and row data update.
				 * 
				 * @param object tr
				 * @param objectdata
				 * @param int rowIndex
				 */
				var fnCreatedRowCallback = function (tr, data, rowIndex) {
					$(tr).attr('data-uuid', data.UUID);
					init_context(tr);
					// Details from external source
					if ('detailSource' in data) {
						$(tr).children('td:first').addClass('toggle-childrow').data('detailSource', data.detailSource);
					}
					$(tr).children().each(function (columnIndex, td) {
						// Init custom buttons in rows
						$(td).children('a.post').each(function (i, a) {
							$(a).attr('data-tableid', '<?= $this->dataTableId; ?>');
						});
					});
				};
				/**
				 * Called after ajax load complete.
				 * 
				 * @param object json
				 * @returns object
				 */
				var fnAjaxUpdateCallback = function (json) {
					fnSetLastUpdate(json.lastUpdate);
					var tableId = '#<?= $this->dataTableId; ?>';
					var $table = $(tableId);
					var table = $table.DataTable();

					if (json.autoUpdate) {
						var timeout = parseInt(json.autoUpdate);
						if (!isNaN(timeout) && timeout < 600000) { // max 10 min
							window.setTimeout(function () {
								$.post(table.ajax.url(), {
									'lastUpdate': fnGetLastUpdate()
								}, function (data, textStatus, jqXHR) {
									fnUpdateDataTable('#<?= $this->dataTableId; ?>', data);
									fnAjaxUpdateCallback(data);
								});
							}, timeout);
						}
					}
					if (json.page) {
						var info = table.page.info();
						// Stay on last page
						if (json.page !== 'last' || info.page + 1 === info.pages) {
							window.setTimeout(function () {
								table.page(json.page).draw(false);
							}, 200);
						}
					} else {
						fnAutoScroll(tableId);
					}
					return json.data;
				};
				// Init DataTable
				var tableId = '#<?= $this->dataTableId; ?>';
				var table = $(tableId).dataTable(<?= $settingsJson; ?>);
				table.fnFilter('<?= str_replace("'", "\'", $this->filter); ?>');
				/**
				 * Toolbar button state update on row (de-)selection.
				 */
				$(tableId + ' tbody').on('click', 'tr', fnUpdateToolbar);
				$('.DTTT_button_text').on('click', fnUpdateToolbar); // (De-)Select all
				$(tableId + '_toolbar').prependTo(tableId + '_wrapper'); // Toolbar above table
				$(tableId + '_toolbar h2.Titel').prependTo(tableId + '_wrapper'); // Title above toolbar
				$('.DTTT_container').children().appendTo(tableId + '_toolbar'); // Buttons inside toolbar
				$('.DTTT_container').remove(); // Remove buttons container
				$(tableId + '_filter input').attr('placeholder', 'Zoeken').unwrap(); // Remove filter container
				$(tableId + '_filter').prependTo(tableId + '_toolbar'); // Filter inside toolbar
				fnInitStickyToolbar(); // Init after modifying DOM
				// Toggle details childrow
				$(tableId + ' tbody').on('click', 'tr td.toggle-childrow', function (event) {
					fnChildRow(tableId, $(this));
				});
				// Group by column
				$(tableId + '.groupByColumn tbody').on('click', 'tr.group', function (event) {
					if (!bShiftPressed && !bCtrlPressed) {
						fnGroupExpandCollapse(tableId, $(this));
					}
				});
				$(tableId + '.groupByColumn thead').on('click', 'th.toggle-group:first', function (event) {
					fnGroupExpandCollapseAll(tableId, $(this));
				});
		<?php if (!$this->groupByLocked) { ?>
					$(tableId + '.groupByColumn').on('order.dt', fnGroupByColumn);
		<?php } ?>
				$(tableId + '.groupByColumn').on('draw.dt', fnGroupByColumnDraw);
				$(tableId + '.groupByColumn').data('collapsedGroups', []);
				if ($(tableId).hasClass('groupByColumn') && fnGetGroupByColumn(tableId)) {
					$(tableId + ' thead tr th').first().addClass('toggle-group toggle-group-expanded');
				}
			});
		</script>
		<?php
	}

}

class DataTableKnop extends FormulierKnop {

	private $multiplicity;
	protected $tableId;

	public function __construct($multiplicity, $tableId, $url, $action, $label, $title, $class, $icon = null) {
		parent::__construct($url, $action . ' DataTableResponse', $label, $title, $icon);
		$this->multiplicity = $multiplicity;
		$this->tableId = $tableId;
		$this->css_classes[] = 'DTTT_button';
		$this->css_classes[] = 'DTTT_button_' . $class;
	}

	public function getUpdateToolbar() {
		return "$('#{$this->getId()}').attr('disabled', !(aantal {$this->multiplicity})).blur().toggleClass('DTTT_disabled', !(aantal {$this->multiplicity}));";
	}

	public function getHtml() {
		return str_replace('<a ', '<a data-tableid="' . $this->tableId . '" ', parent::getHtml());
	}

}

class DataTableResponse extends JsonResponse {

	public $autoUpdate = false;
	public $modal = null;

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo "{\n";
		echo '"modal":' . json_encode($this->modal) . ",\n";
		echo '"autoUpdate":' . json_encode($this->autoUpdate) . ",\n";
		echo '"lastUpdate":' . json_encode(time() - 1) . ",\n";
		echo '"data":[' . "\n";
		$comma = false;
		foreach ($this->model as $model) {
			if ($comma) {
				echo ",\n";
			} else {
				$comma = true;
			}
			$json = $this->getJson($model);
			if ($json) {
				echo $json;
			} else {
				$comma = false;
			}
		}
		echo "\n]}";
	}

}

class RemoveRowsResponse extends DataTableResponse {

	public function getJson($model) {
		return parent::getJson(array(
					'UUID'	 => ( method_exists($entity, 'getUUID') ? $model->getUUID() : $model ),
					'remove' => true
		));
	}

}

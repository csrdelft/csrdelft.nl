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
	private $editable = array();
	protected $settings = array(
		'dom'		 => 'Tfrtpli',
		'tableTools' => array(
			'sRowSelect' => 'os',
			'aButtons'	 => array(
				'select_all',
				'select_none',
				'copy',
				'csv',
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
			$def = $this->model->getAttributeDefinition($attribute);
			switch ($def[0]) {

				case T::Boolean:
				//case T::Integer: // usually in unsupported format and breaks group by
				case T::Float:
					$type = 'html-num-fmt';
					break;

				case T::Date:
				case T::Time:
				case T::DateTime:
				case T::Timestamp:
					$type = 'date';
					break;

				default:
					$type = 'html';
			}

			$this->addColumn($attribute, $type);
		}

		// hide primary key columns
		foreach ($this->model->getPrimaryKey() as $attribute) {
			$this->hideColumn($attribute);
		}
	}

	protected function addKnop(DataTableKnop $knop, $tab = 'head') {
		$this->addFields(array($knop), $tab);
	}

	protected function addColumn($newName, $type = 'string', $before = null) {
		// column definition
		$newColumn = array(
			'name'		 => $newName,
			'data'		 => $newName,
			'title'		 => ucfirst(str_replace('_', ' ', $newName)),
			'type'		 => $type,
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

	protected function editableColumn($name, $url, array $options = null, $editable = true) {
		if ($editable) {
			$this->editable[$name]['url'] = $url;

			if ($options === null) {
				$this->editable[$name]['type'] = 'textarea';
				$this->editable[$name]['onblur'] = 'cancel'; //submit
			} else {
				$this->editable[$name]['type'] = 'select';
				$this->editable[$name]['data'] = json_encode($options);
				$this->editable[$name]['onblur'] = 'cancel';
			}
		} else {
			unset($this->editable[$name]);
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
		}

		// create visible columns index array and default order
		$index = 0;
		$visibleIndex = 0;
		foreach ($this->columns as $name => $def) {
			if (!isset($def['visible']) OR $def['visible'] === true) {

				// translate editable columns index
				if (isset($this->editable[$name])) {
					$this->editable[$visibleIndex] = $this->editable[$name];
					unset($this->editable[$name]);
				}

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
		<?= parent::view(); ?>
		<table id="<?= $this->tableId ?>" class="<?= implode(' ', $this->css_classes) ?>" groupbycolumn="<?= $this->groupByColumn ?>"></table>
		<script type="text/javascript">
			var lastUpdate<?= $this->tableId; ?>;

			$(document).ready(function () {
				var editableColumns = <?= json_encode($this->editable); ?>;

				var fnAjaxUpdateCallback = function (json) {
					lastUpdate<?= $this->tableId; ?> = Math.round(new Date().getTime() / 1000);
					var table = $('#<?= $this->tableId; ?>');
					console.log(json);
					// TODO: remember focus position on update
					/*
					 var oTable = $('#example').dataTable();
					 var keys = new $.fn.dataTable.KeyTable( oTable );
					 keys.fnSetPosition( 1, 1 );
					 */
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
		<?php if ($this->editable) { ?>
						try {
							// voor elke td check of deze editable moet zijn
							$(tr).children().each(function (columnIndex, td) {
								if (columnIndex in editableColumns) {

									// gebruik geen knopjes bij onblur submit
									var onblur = ('submit' !== editableColumns[columnIndex].onblur);

									$(td).addClass('editable').editable(editableColumns[columnIndex].url, {
										type: editableColumns[columnIndex].type,
										data: editableColumns[columnIndex].data,
										onblur: editableColumns[columnIndex].onblur,
										placeholder: '',
										tooltip: 'Klik om te bewerken',
										cssclass: 'InlineForm',
										submit: (onblur ? '<a class="btn submit float-left" title="Invoer opslaan"><img src="<?= CSR_PICS; ?>/famfamfam/accept.png" class="icon" width="16" height="16" /></a>' : ''),
										cancel: (onblur ? '<a class="btn submit float-right" title="Niet opslaan"><img src="<?= CSR_PICS; ?>/famfamfam/delete.png" class="icon" width="16" height="16" /></a>' : ''),
										indicator: '<img src="<?= CSR_PICS; ?>/layout/loading-arrows.gif" class="icon" width="16" height="16" />',
										submitdata: {
											id: data.objectId,
											lastUpdate: lastUpdate<?= $this->tableId; ?>
										},
										onerror: function (settings, original, xhr) {
											console.log(settings);
											console.log(original);
											console.log(xhr);
											console.log(this);
										},
										callback: function (value, settings) {
											console.log(value);
											console.log(settings);
											console.log(this);
										}
									});
								}
							});
						} catch (e) {
							// missing js
						}
		<?php } ?>
				}; // end fnCreatedRowCallback
				var tableId = '#<?= $this->tableId; ?>';
				var oTable = $(tableId).DataTable(<?= $settingsJson; ?>);

				// Keyboard support
				var keys = new $.fn.dataTable.KeyTable($(tableId));
				$(document).keydown(function (event) {
					if (event.keyCode === 32) { // space
						event.preventDefault();
					}
				});
				$(document).keyup(function (event) {
					if (event.keyCode === 32) { // space
						event.preventDefault();
						fnMultiSelect(event, $(keys.fnGetCurrentTD()).parent());
					}
				});

				// Toolbar update on row selection
				var updateToolbar = <?= $this->getUpdateToolbar(); ?>;
				$(tableId).on('draw.dt', updateToolbar);
				$(tableId + ' tbody').on('click', 'tr', updateToolbar);
				$('.DTTT_button_text').on('click', updateToolbar);

				// Toolbar above table
				$(tableId + '_toolbar').prependTo(tableId + '_wrapper');
				$('.DTTT_container').children().appendTo(tableId + '_toolbar');
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
					fnGroupExpandCollapse(oTable, $(tableId), $(this));
				});
				$(tableId + '.groupByColumn thead').on('click', 'tr th.toggle-group', function (event) {
					fnGroupExpandCollapseAll(oTable, $(tableId), $(this));
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
	private function getUpdateToolbar() {
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

	public function getJavascript() {
		$js = "var tableId = '#{$this->tableId}';\n";
		return $js . parent::getJavascript();
	}

}

class DataTableKnop extends FormulierKnop {

	public $onclick;
	private $multiplicity;

	public function __construct($multiplicity, $url, $action, $label, $title, $icon, $float_left = true) {
		parent::__construct($url, $action, $label, $title, $icon, $float_left);
		$this->multiplicity = $multiplicity;
		$this->css_classes[] = 'DTTT_button';
	}

	public function getUpdateToolbar() {
		return <<<JS
$('#{$this->getId()}').attr('disabled', !(aantal {$this->multiplicity}));
$('#{$this->getId()}').toggleClass('DTTT_disabled', $('#{$this->getId()}').prop('disabled'));
JS;
	}

	public function getJavascript() {
		if (isset($this->onclick)) {
			return "$('#{$this->getId()}').unbind('click.onclick').bind('click.onclick', function() {{$this->onclick}});" . parent::getJavascript();
		}
		return parent::getJavascript();
	}

}

class DataTableResponse extends JsonResponse {

	public function getJson($data) {
		return json_encode($data);
	}

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo '{"data":[' . "\n";
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

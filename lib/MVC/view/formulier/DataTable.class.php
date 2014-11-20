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

	protected $orm;
	protected $tableId;
	protected $css_classes = array();
	protected $groupByColumn;
	protected $groupByFixed;
	protected $settings = array(
		'dom'		 => 'frtpli',
		'lengthMenu' => array(
			array(10, 25, 50, 100, -1),
			array(10, 25, 50, 100, 'Alles')
		)
	);
	protected $columns = array();
	protected $defaultLength = false;
	protected $toolbar;
	protected $dataSource;

	public function __construct($orm_class, $tableId, $titel = false, $groupByColumn = null, $groupByFixed = false) {
		parent::__construct(null, $tableId . '_form', null, $titel);

		$this->orm = new $orm_class();
		$this->tableId = $tableId;
		$this->css_classes[] = 'init display';
		$this->groupByColumn = $groupByColumn;
		$this->groupByFixed = $groupByFixed;

		// group by column
		if ($this->groupByFixed) {
			$this->css_classes[] = 'groupByFixed';
		}
		// user may group by
		if ($this->groupByColumn !== false) {
			$this->css_classes[] = 'groupByColumn';
		}

		$this->columns['details'] = array(
			'name'			 => 'details',
			'data'			 => null,
			'title'			 => '',
			'type'			 => 'string',
			'class'			 => 'details-control',
			'orderable'		 => false,
			'searchable'	 => false,
			'defaultContent' => ''
		);
		foreach ($this->orm->getAttributes() as $attribute) {
			$definition = $this->orm->getAttributeDefinition($attribute);
			switch ($definition[0]) {
				case T::DateTime: $type = 'date';
					break;
				case T::Integer: $type = 'num';
					break;
				case T::Float: $type = 'num-fmt';
					break;
				case T::Char: $type = 'string';
					break;
				default: $type = 'html';
					break;
			}
			$this->addColumn($attribute, $type);
		}
		foreach ($this->orm->getPrimaryKey() as $attribute) {
			$this->hideColumn($attribute);
		}

		$this->toolbar = new DataTableToolbar();
		$fields[] = $this->toolbar;
		$this->addFields($fields);
	}

	protected function addColumn($newName, $type = 'html', $before = null) {
		$newColumn = array(
			'name'		 => $newName,
			'data'		 => $newName,
			'title'		 => ucfirst(str_replace('_', ' ', $newName)),
			'type'		 => $type,
			'searchable' => false
		);
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

	protected function getTableHead() {
		return null;
	}

	protected function getTableBody() {
		return null;
	}

	protected function getTableFoot() {
		return null;
	}

	protected function getSettings() {

		if ($this->defaultLength > 0) {
			$this->settings['iDisplayLength'] = $this->defaultLength;
			$this->settings['paging'] = true;
		} else {
			$this->settings['paging'] = false;
			//$settings['scrollX'] = '100%';
			//$settings['scrollY'] = '100%';
		}

		if ($this->dataSource) {
			$this->settings['ajax'] = array(
				'url'	 => $this->dataSource,
				'type'	 => 'POST',
				'data'	 => null
			);
		}

		// get columns index
		$columns = array_keys($this->columns);

		// group by column
		if (isset($this->columns[$this->groupByColumn])) {

			// make group by column invisible
			$this->hideColumn($this->groupByColumn);
			$this->searchColumn($this->groupByColumn);

			// order fixed for group by column
			$index = array_search($this->groupByColumn, $columns);
			$this->settings['orderFixed'] = array(
				array($index, 'asc')
			);

			$this->groupByColumn = $index;
		}

		// default order
		foreach ($this->columns as $name => $def) {
			if ($name == 'details') {
				continue;
			}
			if (!isset($def['visible']) OR $def['visible'] === true) {
				$index = array_search($name, $columns);
				$this->settings['order'] = array(
					array($index, 'asc')
				);
				break;
			}
		}

		$this->settings['columns'] = array_values($this->columns);
		$this->settings['createdRow'] = 'fnCreatedRowCallback';

		return $this->settings;
	}

	public function view() {
		// encode and fix function call
		$settingsJson = json_encode($this->getSettings());
		$settingsJson = str_replace('"fnCreatedRowCallback"', 'fnCreatedRowCallback', $settingsJson);

		// pretty printing
		$settingsJson = str_replace(':{', <<<JSON
:
{
JSON
				, $settingsJson);
		$settingsJson = str_replace(',{', <<<JSON
,
{
JSON
				, $settingsJson);
		$settingsJson = str_replace(',"', <<<JSON
,
"
JSON
				, $settingsJson);
		?>
		<div id="<?= $this->tableId ?>_toolbar" class="dataTables_toolbar"><?= parent::view() ?></div>
		<table id="<?= $this->tableId ?>" class="<?= implode(' ', $this->css_classes) ?>" groupbycolumn="<?= $this->groupByColumn ?>">
			<?= $this->getTableHead() ?>
			<?= $this->getTableBody() ?>
			<?= $this->getTableFoot() ?>
		</table>
		<script type="text/javascript">
			var fnCreatedRowCallback = function (row, data, index) {
				$(row).attr('id', '<?= $this->tableId; ?>_' + index); // data array index
				var primaryKey = ["<?= implode('", "', $this->orm->getPrimaryKey()); ?>"];
				var objectId = [];
				for (var i = 0; i < primaryKey.length; i++) {
					objectId.push(data[primaryKey[i]]);
				}
				$(row).attr('data-objectid', objectId);
				if ('detailSource' in data) {
					$(row).children('td.details-control:first').data('detailSource', data.detailSource);
				} else {
					$(row).children('td.details-control:first').removeClass('details-control');
				}
				try {
					$('abbr.timeago', row).timeago();
				} catch (e) {
					// missing js
				}
			};
			$(document).ready(function () {
				var tableId = '#<?= $this->tableId; ?>';
				var dataTable = $(tableId).DataTable(<?= $settingsJson; ?>);
				// Multiple selection of rows
				$(tableId + ' tbody').on('click', 'tr', function (event) {
					if (!$(event.target).hasClass('details-control')) {
						fnMultiSelect($(this));
					}
					updateToolbar();
				});
				// Opening and closing details
				$(tableId + ' tbody').on('click', 'tr:not(.group) td.details-control', function (event) {
					fnChildRow(dataTable, $(this));
				});
				// Group by column
				$(tableId + '.groupByColumn tbody').on('click', 'tr.group td.details-control', function (event) {
					fnGroupExpandCollapse(dataTable, $(tableId), $(this).parent());
				});
				$(tableId + '.groupByColumn thead').on('click', 'th.details-control', function (event) {
					fnGroupExpandCollapseAll(dataTable, $(tableId), $(this).parent());
				});
				$(tableId + '.groupByColumn:not(.groupByFixed)').on('order.dt', fnGroupByColumn);
				$(tableId + '.groupByColumn').on('draw.dt', fnGroupByColumnDraw);
				$(tableId + '.groupByColumn').data('collapsedGroups', []);
				$(tableId + '.groupByColumn thead tr:first').addClass('expanded');
				if (!$(tableId).hasClass('groupByColumn') || !fnGetGroupByColumn($(tableId))) {
					$(tableId + ' thead tr th.details-control').removeClass('details-control');
				}
				// Toolbar update script
				var updateToolbar = <?= $this->getToolbarUpdateFunction(); ?>;
				$(tableId).on('draw.dt', updateToolbar);
				$(tableId + '_toolbar').prependTo(tableId + '_wrapper');
			});
		</script>
		<?php
	}

	private function getToolbarUpdateFunction() {
		$js = <<<JS
function () {
	var selectie = fnGetSelection(tableId);
	var aantal = selectie.length;
JS;
		foreach ($this->getFields() as $field) {
			if ($field instanceof DataTableToolbar) {
				$js .= "\n" . $field->getUpdateScript() . "\n";
			}
		}
		return $js . '}';
	}

	public function getJavascript() {
		$js = "var tableId = '{$this->tableId}';\n";
		return $js . parent::getJavascript();
	}

}

class DataTableToolbar extends FormKnoppen {

	public function getUpdateScript() {
		$js = '';
		foreach ($this->knoppen as $knop) {
			$js .= $knop->getUpdateScript();
		}
		return $js;
	}

}

class DataTableToolbarKnop extends FormulierKnop {

	public $onclick;
	private $multiplicity;

	public function __construct($multiplicity, $url, $action, $label, $title, $icon, $float_left = true) {
		parent::__construct($url, $action, $label, $title, $icon, $float_left);
		$this->multiplicity = $multiplicity;
	}

	public function getUpdateScript() {
		return "$('#{$this->getId()}').attr('disabled', !(aantal {$this->multiplicity}));";
	}

	public function getJavascript() {
		if (isset($this->onclick)) {
			return "$('#{$this->getId()}').unbind('click.onclick').bind('click.onclick', function() {{$this->onclick}});" . parent::getJavascript();
		}
		return parent::getJavascript();
	}

}

class DataTableResponse extends JsonResponse {

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');
		echo '{"data":[';
		if ($this->model instanceof PDOStatement AND $this->model->rowCount() == 0) {
			// empty
		} else {
			$comma = false;
			foreach ($this->model as $data) {
				if ($comma) {
					echo ',';
				} else {
					$comma = true;
				}
				echo json_encode($data);
			}
		}
		echo ']}';
	}

}

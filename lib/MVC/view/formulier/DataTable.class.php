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
	protected $columns = array();
	protected $columnDefs = array();
	private $groupByColumn;
	private $groupByFixed;
	protected $css_classes = array();
	protected $dataSource;
	protected $defaultLength = 10;
	protected $toolbar;

	public function __construct($orm_class, $tableId, $titel = false, $groupByColumn = true, $groupByFixed = false) {
		parent::__construct(null, $tableId . '_form', null, $titel);

		$this->orm = new $orm_class();
		$this->tableId = $tableId;
		$this->css_classes[] = 'init display';
		$this->groupByColumn = $groupByColumn;
		$this->groupByFixed = $groupByFixed;
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

	/**
	 * Server side processing query
	 */
	protected function getFindJson() {
		$find = '';
		// TODO
		return json_encode($find);
	}

	public function view() {
		$conditionalProps = '';
		$columns = array_keys($this->columns);

		// group by column
		if ($this->groupByFixed) {
			$this->css_classes[] = 'groupByFixed';
		}
		// user may group by
		if ($this->groupByColumn !== false) {
			$this->css_classes[] = 'groupByColumn';

			// existing column?
			if (isset($this->columns[$this->groupByColumn])) {

				// make group by column invisible
				$this->hideColumn($this->groupByColumn);
				$this->searchColumn($this->groupByColumn);

				$idx = array_search($this->groupByColumn, $columns);

				// order fixed for group by column
				$conditionalProps .= ', "orderFixed": [[ ' . $idx . ', "asc"]]'; // FIXME: orderFixed faalt

				$this->groupByColumn = ' groupbycolumn="' . $idx . '"';
			}
		}

		// default order by first visible column
		foreach ($this->columns as $column => $def) {
			if (!isset($def['visible']) OR $def['visible'] === true) {
				if (isset($def['type']) AND $def['type'] === 'date') {
					$conditionalProps .= ', "order": [[ ' . array_search($column, $columns) . ', "asc"]]';
					break;
				}
			}
		}

		// set column definitions
		$conditionalProps .= ', "columnDefs": ' . json_encode(array_values($this->columnDefs));

		// set ajax data source
		if ($this->dataSource) {
			$conditionalProps .= <<<JSON
, "ajax": {
	"url": "{$this->dataSource}",
	"type": "POST",
	"data": {$this->getFindJson()}
}
JSON;
		}
		?>
		<div id="<?= $this->tableId ?>_toolbar" class="dataTables_toolbar"><?= parent::view() ?></div>
		<table id="<?= $this->tableId ?>" class="<?= implode(' ', $this->css_classes) ?>"<?= $this->groupByColumn ?>>
			<?= $this->getTableHead() ?>
			<?= $this->getTableBody() ?>
			<?= $this->getTableFoot() ?>
		</table>
		<script type="text/javascript">
			$(document).ready(function () {
				var tableId = '<?= $this->tableId ?>';
				var table = '#' + tableId;
				var dataTable = $(table).DataTable({
					"iDisplayLength": <?= $this->defaultLength ?>,
					"columns": <?= json_encode(array_values($this->columns)) ?>,
					"createdRow": function (row, data, index) {
						$(row).attr('id', tableId + '_' + index); // data array index
						var primaryKey = ["<?= implode('", "', $this->orm->getPrimaryKey()) ?>"];
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
						} catch ($e) {
							// missing js
						}
					}<?= $conditionalProps ?>
				});
				// Multiple selection of rows
				$(table + ' tbody').on('click', 'tr', function (event) {
					if (!$(event.target).hasClass('details-control')) {
						fnMultiSelect($(this));
					}
					updateToolbar();
				});
				// Opening and closing details
				$(table + ' tbody').on('click', 'tr:not(.group) td.details-control', function (event) {
					fnChildRow(dataTable, $(this));
				});
				// Group by column
				$(table + '.groupByColumn tbody').on('click', 'tr.group td.details-control', function (event) {
					fnGroupExpandCollapse(dataTable, $(table), $(this).parent());
				});
				$(table + '.groupByColumn thead').on('click', 'th.details-control', function (event) {
					fnGroupExpandCollapseAll(dataTable, $(table), $(this).parent());
				});
				$(table + '.groupByColumn:not(.groupByFixed)').on('order.dt', fnGroupByColumn);
				$(table + '.groupByColumn').on('draw.dt', fnGroupByColumnDraw);
				$(table + '.groupByColumn').data('collapsedGroups', []);
				$(table + '.groupByColumn thead tr:first').addClass('expanded');
				if (!$(table).hasClass('groupByColumn') || !fnGetGroupByColumn($(table))) {
					$(table + ' thead tr th.details-control').removeClass('details-control');
				}
				// Toolbar update script
				var updateToolbar = <?= $this->getToolbarUpdateFunction() ?>;
				$(table).on('draw.dt', updateToolbar);
				$(table + '_toolbar').prependTo(table + '_wrapper');
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

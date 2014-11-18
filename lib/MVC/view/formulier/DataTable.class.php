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
	private $groupByColumn;
	protected $css_classes = array();
	protected $dataSource;

	public function __construct($orm_class, $tableId, $groupByColumn = true, $groupByFixed = false, $titel = false) {
		parent::__construct(null, $tableId . '_form', null, $titel);
		$this->orm = new $orm_class();
		$this->tableId = $tableId;
		$this->css_classes[] = 'init display';
		if ($groupByColumn === true) {
			$this->css_classes[] = 'groupByColumn';
			$this->groupByColumn = null;
		} elseif (is_int($groupByColumn)) {
			$this->css_classes[] = 'groupByColumn';
			if ($groupByFixed) {
				$this->css_classes[] = 'groupByFixed';
			}
			$this->groupByColumn = $groupByColumn;
		} else {
			$this->groupByColumn = null;
		}
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

	protected function getColumnsDef() {
		$columns = array();
		$columns['details'] = array(
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
			$columns[$attribute] = array(
				'name'	 => $attribute,
				'data'	 => $attribute,
				'title'	 => ucfirst(str_replace('_', ' ', $attribute)),
				'type'	 => $type
			);
		}
		return $columns;
	}

	private function getConditionalProps() {
		$json = '';
		if ($this->dataSource) {
			$json .= <<<JSON
	, "ajax": "{$this->dataSource}"
JSON;
		}
		if ($this->groupByColumn) {
			$json .= <<<JSON
	, "columnDefs": [
		{
			"visible": false,
			"targets": [{$this->groupByColumn}]
		}
	]
	, "orderFixed": [[{$this->groupByColumn}, "asc"]]
JSON;
		}
		return $json;
	}

	public function view() {
		?>
		<div id="<?= $this->tableId ?>_toolbar" class="dataTables_toolbar"><?= parent::view() ?></div>
		<table id="<?= $this->tableId ?>" class="<?= implode(' ', $this->css_classes) ?>" groupByColumn="<?= $this->groupByColumn ?>">
			<?= $this->getTableHead() ?>
			<?= $this->getTableBody() ?>
			<?= $this->getTableFoot() ?>
		</table>
		<script type="text/javascript">
			$(document).ready(function () {
				var tableId = '<?= $this->tableId ?>';
				var table = '#' + tableId;
				var dataTable = $(table).DataTable({
					"columns": <?= json_encode(array_values($this->getColumnsDef())) ?>,
					"order": [[1, "asc"]],
					"createdRow": function (row, data, index) {
						$(row).attr('id', tableId + '_' + index); // data array index
						if ('detailSource' in data) {
							$(row).children('td.details-control:first').data('detailSource', data.detailSource);
						} else {
							$(row).children('td.details-control:first').removeClass('details-control');
						}
					}<?= $this->getConditionalProps() ?>
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
				var updateToolbar = <?= $this->getToolbarUpdateScript(); ?>;
				$(table).on('draw.dt', updateToolbar);
				$(table + '_toolbar').prependTo(table + '_wrapper');
			});
		</script>
		<?php
	}

	private function getToolbarUpdateScript() {
		$js = <<<JS
function () {
	var aantal = $(table + ' tbody tr.selected').length;
JS;
		foreach ($this->getFields() as $field) {
			if ($field instanceof DataTableToolbar) {
				$js .= $field->getUpdateScript() . "\n";
			}
		}
		return $js . '}';
	}

}

class DataTableToolbar extends FormKnoppen {

	public function getUpdateScript() {
		foreach ($this->knoppen as $knop) {
			return $knop->getUpdateScript();
		}
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
		return <<<JS
$('#{$this->getId()}').attr('disabled', !(aantal {$this->multiplicity}));
JS;
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
$('#{$this->getId()}').click(function () {
	{$this->onclick}
});
JS;
	}

}

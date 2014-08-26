<?php

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
class DataTable implements View {

	private $tableId;
	private $groupByColumn;
	protected $css_classes = array();
	protected $dataSource;
	protected $columns = array('name', 'position', 'salary', 'start_date', 'office', 'extn'); // TODO

	public function __construct($tableId, $groupByColumn = true, $groupByFixed = false) {
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

	public function setDataSource($url) {
		$this->dataSource = $url;
	}

	public function getModel() {
		return null;
	}

	public function getTitel() {
		return null;
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
		if ($this->getTitel()) {
			echo '<h2>' . $this->getTitel() . '</h2>';
		}
		?>
		<div id="<?= $this->tableId ?>_toolbar" class="dataTables_toolbar">
			<button id="rowcount">Count selected rows</button>
		</div>
		<table id="<?= $this->tableId ?>" class="<?= implode(' ', $this->css_classes) ?>" groupByColumn="<?= $this->groupByColumn ?>">
			<?= $this->getTableHead() ?>
			<?= $this->getTableBody() ?>
			<?= $this->getTableFoot() ?>
		</table>
		<script type="text/javascript">
			$(document).ready(function() {
				var tableId = '<?= $this->tableId ?>';
				var table = '#' + tableId;
				var dataTable = $(table).DataTable({
					"columns": [
						{
							"name": "details",
							"data": null,
							"title": "",
							"type": "string",
							"class": "details-control",
							"orderable": false,
							"searchable": false,
							"defaultContent": ""
						},
						{
							"name": "name",
							"title": "Name",
							"data": "name",
							"type": "html"
						},
						{
							"name": "position",
							"title": "Position",
							"data": "position",
							"type": "string"
						},
						{
							"name": "office",
							"title": "Office",
							"data": "office",
							"type": "string"
						},
						{
							"name": "salary",
							"title": "Salary",
							"data": "salary",
							"type": "num-fmt"
						},
						{
							"name": "start_date",
							"title": "Start date",
							"data": "start_date",
							"type": "date"
						},
						{
							"name": "extn",
							"title": "Ext.no",
							"data": "extn",
							"type": "num"
						}
					],
					"order": [[1, "asc"]],
					"createdRow": function(row, data, index) {
						$(row).attr('id', tableId + '_' + index); // data array index
						if ('detailSource' in data) {
							$(row).children('td.details-control:first').data('detailSource', data.detailSource);
						} else {
							$(row).children('td.details-control:first').removeClass('details-control');
						}
					}<?= $this->getConditionalProps() ?>
				});
				// Multiple selection of rows
				$(table + ' tbody').on('click', 'tr', function(event) {
					if (!$(event.target).hasClass('details-control')) {
						fnMultiSelect($(this));
					}
					updateToolbar();
				});
				// Opening and closing details
				$(table + ' tbody').on('click', 'tr:not(.group) td.details-control', function(event) {
					fnChildRow(dataTable, $(this));
				});
				// Group by column
				$(table + '.groupByColumn tbody').on('click', 'tr.group td.details-control', function(event) {
					fnGroupExpandCollapse(dataTable, $(table), $(this).parent());
				});
				$(table + '.groupByColumn thead').on('click', 'th.details-control', function(event) {
					fnGroupExpandCollapseAll(dataTable, $(table), $(this).parent());
				});
				$(table + '.groupByColumn:not(.groupByFixed)').on('order.dt', fnGroupByColumn);
				$(table + '.groupByColumn').on('draw.dt', fnGroupByColumnDraw);
				$(table + '.groupByColumn').data('collapsedGroups', []);
				$(table + '.groupByColumn thead tr:first').addClass('expanded');
				$(table + ':not(.groupByColumn) th.details-control').removeClass('details-control');
				// Setup toolbar
				$(table + '_toolbar').insertBefore(table);
				var updateToolbar = function() {
					var aantal = $(table + ' tbody tr.selected').length;
					$(table + '_toolbar #rowcount').attr('disabled', aantal < 1);
				};
				$(table).on('draw.dt', updateToolbar);
				$(table + '_toolbar #rowcount').click(function() {
					alert($(table + ' tbody tr.selected').length + ' row(s) selected');
				});
			});
		</script>
		<?php
	}

}

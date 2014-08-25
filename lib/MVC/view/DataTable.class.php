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

	protected $model;
	protected $tableId;
	protected $css_classes = array();
	public $titel;

	public function __construct($model, $tableId, $titel = false) {
		$this->model = $model;
		$this->tableId = $tableId;
		$this->css_classes[] = 'init display groupByColumn'; // default group by
		$this->titel = $titel;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getModel() {
		return $this->model;
	}

	public function view() {
		if ($this->getTitel()) {
			echo '<h2>' . $this->getTitel() . '</h2>';
		}
		?>
		<div id="<?= $this->tableId ?>_toolbar" class="dataTables_toolbar">
			<button id="rowcount" class="btn btn-primary">Count selected rows</button>
		</div>
		<table id="<?= $this->tableId ?>" class="<?= implode(' ', $this->css_classes) ?>" groupByColumn="3"><!-- // default group by -->
			<thead>
				<tr>
					<th></th>
					<th>Name</th>
					<th>Position</th>
					<th>Office</th>
					<th>Salary</th>
					<th>Start date</th>
					<th>Ext.n&ordm;</th>
				</tr>
			</thead>
		</table>
		<script type="text/javascript">
			$(document).ready(function() {
				var tableId = '<?= $this->tableId ?>';
				var table = '#' + tableId;
				var dataTable = $(table).DataTable({
					"ajax": "/layout3/example-data.json",
					"columns": [
						{
							"class": "details-control",
							"orderable": false,
							"searchable": false,
							"defaultContent": ""
						},
						{
							"data": "name"
						},
						{
							"data": "position"
						},
						{
							"data": "office",
							"visible": false // default group by
						},
						{
							"data": "salary"
						},
						{
							"data": "start_date",
							"render": function(data, type, row) {
								var date = Date.parse(data);
								if (date < new Date("March 21, 2010")) {
									return data;
								}
								return '<abbr class="timeago" title="Recent">' + data + '</abbr>';
							}
						},
						{
							"data": "extn"
						}
					],
					"order": [[getGroupByColumn($(table)) | 1, "asc"]], // default group by
					"createdRow": function(row, data, index) {
						$(row).attr('id', tableId + '_' + index);
						$(row).children(':first').attr('href', '/onderhoud.html?name=' + encodeURI(data.name));
					}
				});
				// Multiple selection of rows
				$(table + ' tbody').on('click', 'tr', function(event) {
					if (!$(event.target).hasClass('details-control')) {
						multiSelect($(this));
					}
					$(table).trigger('draw.dt', [event, dataTable.settings()]);
				});
				// Opening and closing details
				$(table + ' tbody').on('click', 'td.details-control', function(event) {
					childRow(dataTable, $(this));
				});
				// Group by column
				$(table + '.groupByColumn').on('order.dt', groupByColumn);
				$(table + '.groupByColumn').on('draw.dt', groupByColumnDraw);
				// Setup toolbar
				$(table).on('draw.dt', function(e, settings) {
					var aantal = $(table + ' tbody tr.selected').length;
					$(table + '_toolbar #rowcount').toggleClass('disabled', aantal < 1);
				});
				$(table + '_toolbar').insertBefore(table);
				$(table + '_toolbar #rowcount').click(function() {
					alert($(table + ' tbody tr.selected').length + ' row(s) selected');
				});
			});
		</script>
		<?php
	}

}

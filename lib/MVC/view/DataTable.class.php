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
	public $titel;

	public function __construct($model, $tableId, $titel = false) {
		$this->model = $model;
		$this->tableId = $tableId;
		$this->titel = $titel;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getModel() {
		return $this->model;
	}

	public function getJavascript() {
		return <<<JS
$(document).ready(function() {
	var dataTable = $('#{$this->tableId}').DataTable({
		"ajax": '/layout3/{$this->tableId}-data.json',
		"columns": [
			{
				"class": 'details-control',
				"orderable": false,
				"searchable": false,
				"defaultContent": ''
			},
			{
				"data": "name"
			},
			{
				"data": "position"
			},
			{
				"data": "office"
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
		"order": [[getGroupByColumn('#{$this->tableId}') | 1, "asc"]],
		"createdRow": function(row, data, index) {
			$(row).attr('id', '{$this->tableId}_' + index);
			$(row).children(':first').attr('href', '/onderhoud.html?name=' + encodeURI(data.name));
		}
	});
	// Opening and closing details
	$('#{$this->tableId} tbody').on('click', 'td.details-control', function() {
		childRow($(this), dataTable);
	});
	// Multiple selection of rows
	$('#{$this->tableId} tbody').on('click', 'tr', function() {
		multiSelect($(this));
		$('#{$this->tableId}').trigger('draw.dt');
	});
	// Setup toolbar
	$('#{$this->tableId}').on('draw.dt', function() {
		var aantal = $('#{$this->tableId} tbody tr.selected').length;
		$('#{$this->tableId}_toolbar #rowcount').toggleClass('disabled', aantal < 1);
	});
	$('#{$this->tableId}_toolbar').insertBefore('#{$this->tableId}');
	$('#{$this->tableId}_toolbar #rowcount').click(function() {
		alert($('#{$this->tableId} tbody tr.selected').length + ' row(s) selected');
	});
});
JS;
	}

	public function getToolbarDiv() {
		return <<<HTML
<div id="{$this->tableId}_toolbar" class="dataTables_toolbar">
	<button id="rowcount" class="btn btn-primary">Count selected rows</button>
</div>
HTML;
	}

	public function view() {
		if ($this->getTitel()) {
			echo '<h2>' . $this->getTitel() . '</h2>';
		}
		echo $this->getToolbarDiv();
		echo <<<HTML
<table id="{$this->tableId}" class="display">
	<thead>
		<tr>
			<th></th>
			<th>Name</th>
			<th>Position</th>
			<th>Office</th>
			<th>Salary</th>
			<th>Start date</th>
			<th>Extn</th>
		</tr>
	</thead>
</table>
HTML;
		echo '<script type="text/javascript">' . $this->getJavascript() . '</script>';
	}

}

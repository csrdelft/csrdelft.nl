<?php

namespace CsrDelft\view\formulier\datatable;

use function CsrDelft\classNameZonderNamespace;
use CsrDelft\view\formulier\elementen\FormElement;
use CsrDelft\view\View;

/**
 * DataTable.php
 *
 * @author P.W.G. Brussee <brussee@live.nl
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Uses DataTables plug-in for jQuery.
 * @see http://www.datatables.net/
 *
 */
class DataTable implements View, FormElement {

	public $nestedForm = true;
	public $filter = null;
	protected $dataUrl;
	private $groupByColumn;
	protected $titel;
	protected $dataTableId;
	public $model;
	protected $defaultLength = 10;
	private $columns = array();
	protected $settings = array(
		'deferRender' => true,
		'dom' => 'Bfrtpli',
		'buttons' => array('copy', 'csv', 'excel', 'print'),
		'userButtons' => array(),
		'select' => true,
		'lengthMenu' => array(
			array(10, 25, 50, 100, -1),
			array(10, 25, 50, 100, 'Alles')
		),
		'language' => array(
			'sProcessing' => 'Bezig...',
			'sLengthMenu' => '_MENU_ resultaten weergeven',
			'sZeroRecords' => 'Geen resultaten gevonden',
			'sInfo' => '_START_ tot _END_ van _TOTAL_ resultaten',
			'sInfoEmpty' => 'Geen resultaten om weer te geven',
			'sInfoFiltered' => ' (gefilterd uit _MAX_ resultaten)',
			'sInfoPostFix' => '',
			'sSearch' => 'Zoeken',
			'sEmptyTable' => 'Geen resultaten aanwezig in de tabel',
			'sInfoThousands' => '.',
			'sLoadingRecords' => 'Een moment geduld aub - bezig met laden...',
			'oPaginate' => array(
				'sFirst' => 'Eerste',
				'sLast' => 'Laatste',
				'sNext' => 'Volgende',
				'sPrevious' => 'Vorige'
			),
			'select' => array(
				'rows' => array(
					'_' => '%d rijen geselecteerd',
					'0' => '',
					'1' => '1 rij geselecteerd'
				)
			),
			'buttons' => array(
				'copy' => 'Kopiëren',
				'print' => 'Printen'
			),
			// Eigen definities
			'csr' => array(
				'zeker' => 'Weet u het zeker?'
			)
		)
	);

	public function __construct($orm, $dataUrl, $titel = false, $groupByColumn = null) {
		$this->model = new $orm();
		$this->titel = $titel;

		$this->dataUrl = $dataUrl;
		$this->dataTableId = uniqid(classNameZonderNamespace(get_class($this->model)));
		$this->groupByColumn = $groupByColumn;

		// create group expand / collapse column
		$this->columns['details'] = array(
			'name' => 'details',
			'data' => 'details',
			'title' => '',
			'type' => 'string',
			'orderable' => false,
			'searchable' => false,
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

	/**
	 * @return string
	 */
	public function getDataTableId() {
		return $this->dataTableId;
	}

	protected function addKnop(DataTableKnop $knop) {
		$this->settings['userButtons'][] = $knop;
	}

	protected function columnPosition($name) {
		return array_search($name, array_keys($this->columns));
	}

	protected function setOrder($names) {
		$orders = [];
		foreach ($names as $name => $order) {
			$orders[] = array($this->columnPosition($name), $order);
		}
		$this->settings['order'] = $orders;
	}

	protected function addColumn($newName, $before = null, $defaultContent = null, $render = null, $order_by = null, $type = 'string') {
		// column definition
		$newColumn = array(
			'name' => $newName,
			'data' => $newName,
			'title' => ucfirst(str_replace('_', ' ', $newName)),
			'defaultContent' => $defaultContent,
			'type' => $type,
			'searchable' => false,
			'render' => $render
			/*
			  //TODO: sort by other column
			  { "iDataSort": 1 },
			  reldate(getDateTime());
			 */
		);
		if ($order_by !== null) {
			$newColumn['orderData'] = $this->columnPosition($order_by);
		}
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

	/**
	 * Gebruik deze functie om kolommen te verwijderen, doe dit als eerst.
	 *
	 * @see columnPosition geeft een andere uitvoer na deze functie.
	 *
	 * Gebruik de veiligere @see hideColumn als je de inhoud van een kolom nog wil kunnen opvragen.
	 *
	 * @param $name
	 */
	protected function deleteColumn($name) {
		if (isset($this->columns[$name])) {
			array_splice($this->columns, $this->columnPosition($name), 1);
		}
	}

	protected function hideColumn($name, $hide = true) {
		if (isset($this->columns[$name])) {
			$this->columns[$name]['visible'] = !$hide;
		}
	}

	protected function searchColumn($name, $searchable = true) {
		if (isset($this->columns[$name])) {
			$this->columns[$name]['searchable'] = (boolean)$searchable;
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
			$this->settings['pageLength'] = $this->defaultLength;
		} else {
			$this->settings['paging'] = false;
			$this->settings['dom'] = str_replace('i', '', $this->settings['dom']);
		}

		// set ajax url
		if ($this->dataUrl) {
			$this->settings['ajax'] = array(
				'url' => $this->dataUrl,
				'type' => 'POST',
				'data' => array(
					'lastUpdate' => 'fnGetLastUpdate'
				),
				'dataSrc' => 'fnAjaxUpdateCallback'
			);
		}
		$this->settings['createdRow'] = 'fnCreatedRowCallback';

		// group by column
		if (isset($this->columns[$this->groupByColumn])) {
			// make group by column invisible and searchable
			$this->hideColumn($this->groupByColumn);
			$this->searchColumn($this->groupByColumn);

			$groupByColumnPosition = $this->columnPosition($this->groupByColumn);
			$this->settings['columnGroup'] = [ 'column' => $groupByColumnPosition ];
			$this->settings['orderFixed'] = [
				[$groupByColumnPosition, 'asc']
			];
		}

		// create visible columns index array and default order
		$index = 0;
		$visibleIndex = 0;
		foreach ($this->columns as $name => $def) {
			if (!isset($def['visible']) OR $def['visible'] === true) {

				// default order by first visible orderable column
				if (!isset($this->settings['order']) AND !(isset($def['orderable']) AND $def['orderable'] === false)) {
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

		// Voeg nieuwe knoppen toe
		$this->settings['buttons'] = array_merge($this->settings['userButtons'], $this->settings['buttons']);

		return $this->settings;
	}

	public function view() {
		echo $this->getHtml();
		// Voorkom globals in javascript
		echo <<<HTML
<script type="text/javascript">
	(function() {
		{$this->getJavascript()}
	})();
</script>
HTML;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getBreadcrumbs() {
		return $this->titel;
	}

	/**
	 * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
	 */
	public function getModel() {
		return $this->model;
	}

	public function getType() {
		return classNameZonderNamespace(get_class($this));
	}

	public function getHtml() {
		return <<<HTML
<h2 class="Titel">{$this->getTitel()}</h2>

<table id="{$this->dataTableId}" class="display"></table>
HTML;
	}

	public function getJavascript() {
		// encode settings
		$settingsJson = json_encode($this->getSettings(), DEBUG ? JSON_PRETTY_PRINT : 0);

		// js function calls
		$settingsJson = str_replace('"fnGetLastUpdate"', 'fnGetLastUpdate', $settingsJson);
		$settingsJson = str_replace('"fnAjaxUpdateCallback"', 'fnAjaxUpdateCallback', $settingsJson);
		$settingsJson = str_replace('"fnCreatedRowCallback"', 'fnCreatedRowCallback', $settingsJson);
		$settingsJson = preg_replace('/"render":\s?"(.+?)"/', '"render": $1', $settingsJson);

		$filter = str_replace("'", "\'", $this->filter);

		return <<<JS
			$(document).ready(function () {
				var tableId = '#{$this->dataTableId}';
			
				var fnGetLastUpdate = function () {
					return Number($(tableId).attr('data-lastupdate'));
				};
				var fnSetLastUpdate = function (lastUpdate) {
					$(tableId).attr('data-lastupdate', lastUpdate);
				};
				/**
				 * Called after row addition and row data update.
				 *
				 * @param tr
				 * @param data
				 * @param rowIndex
				 */
				var fnCreatedRowCallback = function (tr, data, rowIndex) {
					var table = this;
					$(tr).attr('data-uuid', data.UUID);
					init_context(tr);
					
					$(tr).children().each(function (columnIndex, td) {
						// Init custom buttons in rows
						$(td).children('a.post').each(function (i, a) {
							$(a).attr('data-tableid', table.attr('id'));
						});
					});
				};
				/**
				 * Called after ajax load complete.
				 *
				 * @param json
				 * @returns object
				 */
				var fnAjaxUpdateCallback = function (json) {
					fnSetLastUpdate(json.lastUpdate);
					var table = $(tableId).DataTable();

					if (json.autoUpdate) {
						var timeout = parseInt(json.autoUpdate);
						if (!isNaN(timeout) && timeout < 600000) { // max 10 min
							window.setTimeout(function () {
								$.post(table.ajax.url(), {
									'lastUpdate': fnGetLastUpdate()
								}, function (data, textStatus, jqXHR) {
									fnUpdateDataTable(tableId, data);
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
					fnDeselectItemsOnPageChange(table);
					return json.data;
				};
				// Init DataTable
				var jtable = $(tableId);
				var table = jtable.dataTable({$settingsJson});
				table.fnFilter('{$filter}');
			});

			var fnDeselectItemsOnPageChange = function (dt) {
				dt.on('page', function() {
					dt.rows({ selected: true }).deselect();
				});
			};
JS;
	}
}

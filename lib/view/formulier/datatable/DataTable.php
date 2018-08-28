<?php

namespace CsrDelft\view\formulier\datatable;

use CsrDelft\Orm\PersistenceModel;
use CsrDelft\view\formulier\datatable\knop\DataTableKnop;
use CsrDelft\view\formulier\elementen\FormElement;
use CsrDelft\view\View;

/**
 * @author P.W.G. Brussee <brussee@live.nl
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Uses DataTables plug-in for jQuery.
 * @see http://www.datatables.net/
 *
 */
class DataTable implements View, FormElement {

	public $filter = null;
	/** @var PersistenceModel */
	public $model;

	protected $dataUrl;
	protected $titel;
	protected $dataTableId;
	protected $defaultLength = 10;
	protected $settings = [
		'dom' => 'Bfrtpli',
		'buttons' => ['copy', 'csv', 'excel', 'print'],
		'userButtons' => [],
		'select' => true,
	];

	private $columns = array();
	private $groupByColumn;

	public function __construct($orm, $dataUrl, $titel = false, $groupByColumn = null) {
		$this->model = new $orm();
		$this->titel = $titel;

		$this->dataUrl = $dataUrl;
		$this->dataTableId = uniqid_safe(classNameZonderNamespace(get_class($this->model)));
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

	/**
	 * @param DataTableKnop $knop
	 */
	protected function addKnop(DataTableKnop $knop) {
		$knop->setDataTableId($this->dataTableId);
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

	/**
	 * @param string $newName
	 * @param string|null $before
	 * @param string|null $defaultContent
	 * @param CellRender|null $render
	 * @param string|null $order_by
	 * @param CellType|null $type
	 * @param string|null $data The data source for the column. Defaults to the column name.
	 */
	protected function addColumn($newName, $before = null, $defaultContent = null, CellRender $render = null, $order_by = null, CellType $type = null, $data = null) {
		$type = $type ?: CellType::String();
		$render = $render ?: CellRender::None();

		// column definition
		$newColumn = array(
			'name' => $newName,
			'data' => $data ?? $newName,
			'title' => ucfirst(str_replace('_', ' ', $newName)),
			'defaultContent' => $defaultContent,
			'type' => $type,
			'searchable' => false,
			'render' => $render->getChoice()
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
	 * @param string $name
	 */
	protected function deleteColumn($name) {
		if (isset($this->columns[$name])) {
			array_splice($this->columns, $this->columnPosition($name), 1);
		}
	}

	/**
	 * @param string $name
	 * @param bool $hide
	 */
	protected function hideColumn($name, $hide = true) {
		if (isset($this->columns[$name])) {
			$this->columns[$name]['visible'] = !$hide;
		}
	}

	/**
	 * @param string $name
	 * @param bool $searchable
	 */
	protected function searchColumn($name, $searchable = true) {
		if (isset($this->columns[$name])) {
			$this->columns[$name]['searchable'] = (boolean)$searchable;
		}
	}

	/**
	 * @param string $name
	 * @param string $title
	 */
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

		// group by column
		if (isset($this->columns[$this->groupByColumn])) {
			// make group by column invisible and searchable
			$this->hideColumn($this->groupByColumn);
			$this->searchColumn($this->groupByColumn);

			$groupByColumnPosition = $this->columnPosition($this->groupByColumn);
			$this->settings['columnGroup'] = ['column' => $groupByColumnPosition];
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
		echo <<<HTML
<script type="text/javascript">
		{$this->getScript()}
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

	public function getScript() {
		// encode settings
		$settingsJson = json_encode($this->getSettings(), DEBUG ? JSON_PRETTY_PRINT : 0);

		// js function calls
		$settingsJson = str_replace('"fnGetLastUpdate"', 'fnGetLastUpdate', $settingsJson);
		$settingsJson = str_replace('"fnAjaxUpdateCallback"', 'fnAjaxUpdateCallback', $settingsJson);
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
								}, function (data) {
									fnUpdateDataTable(tableId, data);
									fnAjaxUpdateCallback(data);
								});
							}, timeout);
						}
					}
					
					fnAutoScroll(tableId);
					
					return json.data;
				};
				// Init DataTable
				var jtable = $(tableId);
				var table = jtable.dataTable({$settingsJson});
				table.api().search('{$filter}');
				
				table.on('page', function() {
				  table.rows({selected: true}).deselect();
				})
			});
JS;
	}

	public function getJavascript() {
		//Nothing should be returned here because the script is already embedded in getView
		return "";
	}
}

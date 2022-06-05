<?php

namespace CsrDelft\view\datatable;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Doctrine\Type\DateTimeImmutableType;
use CsrDelft\Component\DataTable\CustomDataTableEntry;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\formulier\FormElement;
use CsrDelft\view\ToHtmlResponse;
use CsrDelft\view\ToResponse;
use CsrDelft\view\View;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * @author P.W.G. Brussee <brussee@live.nl
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Bouwt een configuratie voor een datatable.
 *
 * Uses DataTables plug-in for jQuery.
 * @see http://www.datatables.net/
 *
 */
class DataTable implements View, FormElement, ToResponse
{
	use ToHtmlResponse;
	const POST_SELECTION = 'DataTableSelection';

	protected $dataUrl;
	protected $titel;
	protected $dataTableId;
	protected $defaultLength = 10;
	protected $selectEnabled = true;
	protected $vliegendeKnoppen = false;
	protected $settings = [
		'dom' => 'Bfrtpli',
		'buttons' => [
			[
				'extend' => 'copy',
				'exportOptions' => [
					'columns' => ':visible',
					'orthogonal' => 'export',
				],
			],
			[
				'extend' => 'csv',
				'exportOptions' => [
					'columns' => ':visible',
					'orthogonal' => 'export',
				],
			],
			[
				'extend' => 'excel',
				'exportOptions' => [
					'columns' => ':visible',
					'orthogonal' => 'export',
				],
			],
			[
				'extend' => 'print',
				'exportOptions' => [
					'columns' => ':visible',
					'orthogonal' => 'export',
				],
			],
		],
		'userButtons' => [],
		'rowButtons' => [],
	];

	private $columns = [];
	private $groupByColumn;

	public function __construct(
		$orm,
		$dataUrl,
		$titel = false,
		$groupByColumn = null,
		$loadColumns = true
	) {
		$this->titel = $titel;

		$this->dataUrl = $dataUrl;
		$this->dataTableId = uniqid_safe(classNameZonderNamespace($orm));
		$this->groupByColumn = $groupByColumn;

		if ($titel) {
			$this->settings['buttons'][1]['filename'] = $titel;
			$this->settings['buttons'][2]['filename'] = $titel;
		}

		// create group expand / collapse column
		$this->columns['details'] = [
			'name' => 'details',
			'data' => 'details',
			'title' => '',
			'type' => 'string',
			'orderable' => false,
			'searchable' => false,
			'defaultContent' => '',
		];

		if ($loadColumns) {
			$this->loadColumns($orm);
		}
	}

	/**
	 * @return string
	 */
	public function getDataTableId()
	{
		return $this->dataTableId;
	}

	public function setSearch($searchString)
	{
		$this->settings['search'] = ['search' => $searchString];
	}

	/**
	 * @param $orm
	 * @throws Exception
	 */
	public function loadColumns($orm): void
	{
		if (is_a($orm, CustomDataTableEntry::class, true)) {
			foreach ($orm::getFieldNames() as $attribute) {
				$this->addColumn($attribute);
			}

			foreach ($orm::getIdentifierFieldNames() as $attribute) {
				$this->hideColumn($attribute);
			}
		} else {
			$manager = ContainerFacade::getContainer()
				->get('doctrine')
				->getManager();
			/** @var ClassMetadata $metadata */
			$metadata = $manager->getClassMetaData($orm);

			// generate columns from entity attributes
			foreach ($metadata->getFieldNames() as $attribute) {
				$type = Type::getTypeRegistry()->get(
					$metadata->getTypeOfField($attribute)
				);
				$columnName = $metadata->getColumnName($attribute);
				if ($type instanceof DateTimeImmutableType) {
					$this->addColumn($columnName, null, null, CellRender::DateTime());
				} elseif ($type instanceof BooleanType) {
					$this->addColumn($columnName, null, null, CellRender::Check());
				} else {
					$this->addColumn($columnName);
				}
			}

			// hide primary key columns
			foreach ($metadata->getIdentifierColumnNames() as $attribute) {
				$this->hideColumn($attribute);
			}
		}
	}

	/**
	 * @param DataTableKnop $knop
	 */
	protected function addKnop(DataTableKnop $knop)
	{
		$knop->setDataTableId($this->dataTableId);
		$this->settings['userButtons'][] = $knop;
	}

	protected function addRowKnop(DataTableRowKnop $knop)
	{
		$this->settings['rowButtons'][] = $knop;
	}

	protected function columnPosition($name)
	{
		return array_search($name, array_keys($this->columns));
	}

	protected function setOrder($names)
	{
		$orders = [];
		foreach ($names as $name => $order) {
			$orders[] = [$this->columnPosition($name), $order];
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
	protected function addColumn(
		$newName,
		$before = null,
		$defaultContent = null,
		CellRender $render = null,
		$order_by = null,
		CellType $type = null,
		$data = null
	) {
		$type = $type ?: CellType::String();
		$render = $render ?: CellRender::Default();

		// column definition
		$newColumn = [
			'name' => $newName,
			'data' => $data ?? $newName,
			'title' => ucfirst(str_replace('_', ' ', $newName)),
			'defaultContent' => $defaultContent,
			'type' => $type,
			'searchable' => false,
			'render' => $render->getChoice(),
			/*
			  //TODO: sort by other column
			  { "iDataSort": 1 },
			  reldate(getDateTime());
			 */
		];
		if ($order_by !== null) {
			$newColumn['orderData'] = $this->columnPosition($order_by);
		}
		// append or insert at position
		if ($before === null) {
			$this->columns[$newName] = $newColumn;
		} else {
			$array = [];
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
	protected function deleteColumn($name)
	{
		if (isset($this->columns[$name])) {
			array_splice($this->columns, $this->columnPosition($name), 1);
		}
	}

	/**
	 * @param string $name
	 * @param bool $hide
	 */
	protected function hideColumn($name, $hide = true)
	{
		if (isset($this->columns[$name])) {
			$this->columns[$name]['visible'] = !$hide;
		}
	}

	/**
	 * @param string $name
	 * @param bool $searchable
	 */
	protected function searchColumn($name, $searchable = true)
	{
		if (isset($this->columns[$name])) {
			$this->columns[$name]['searchable'] = (bool) $searchable;
		}
	}

	/**
	 * @param string $name
	 * @param string $title
	 */
	protected function setColumnTitle($name, $title)
	{
		if (isset($this->columns[$name])) {
			$this->columns[$name]['title'] = $title;
		}
	}

	protected function getSettings()
	{
		$settings = $this->settings;

		// set view modus: paging or scrolling
		if ($this->defaultLength > 0) {
			$settings['paging'] = true;
			$settings['pageLength'] = $this->defaultLength;
		} else {
			$settings['paging'] = false;
			$settings['dom'] = str_replace('i', '', $this->settings['dom']);
		}

		$settings['select'] = $this->selectEnabled;

		// set ajax url
		if ($this->dataUrl) {
			$settings['ajax'] = [
				'url' => $this->dataUrl,
				'type' => 'POST',
				'data' => [
					'lastUpdate' => '', // Overridden in datatable.js
				],
				'dataSrc' => '', // Overriden in datatable.js
			];
		}

		// group by column
		if (isset($this->columns[$this->groupByColumn])) {
			// make group by column invisible and searchable
			$this->hideColumn($this->groupByColumn);
			$this->searchColumn($this->groupByColumn);

			$groupByColumnPosition = $this->columnPosition($this->groupByColumn);
			$settings['columnGroup'] = ['column' => $groupByColumnPosition];
			$settings['orderFixed'] = [[$groupByColumnPosition, 'asc']];
		}

		if (count($settings['rowButtons']) > 0) {
			$this->columns['actionButtons'] = [
				'name' => 'actionButtons',
				'searchable' => false,
				'orderable' => false,
				'defaultContent' => '',
			];
		} else {
			// Client checkt of rowButtons bestaat
			unset($settings['rowButtons']);
		}

		// create visible columns index array and default order
		$index = 0;
		$visibleIndex = 0;
		foreach ($this->columns as $name => $def) {
			if (!isset($def['visible']) || $def['visible'] === true) {
				// default order by first visible orderable column
				if (
					!isset($settings['order']) &&
					!(isset($def['orderable']) && $def['orderable'] === false)
				) {
					$settings['order'] = [[$index, 'asc']];
				}

				$visibleIndex++;
			}
			$index++;
		}

		// translate columns index
		$settings['columns'] = array_values($this->columns);

		// Voeg nieuwe knoppen toe
		$settings['buttons'] = array_merge(
			$settings['userButtons'],
			$settings['buttons']
		);

		return $settings;
	}

	public function __toString()
	{
		return $this->getHtml();
	}

	public function getTitel()
	{
		return $this->titel;
	}

	public function getBreadcrumbs()
	{
		return $this->titel;
	}

	/**
	 * Hiermee wordt gepoogt af te dwingen dat een view een model heeft om te tonen
	 */
	public function getModel()
	{
		return null;
	}

	public function getType()
	{
		return classNameZonderNamespace(get_class($this));
	}

	public function getHtml()
	{
		$id = str_replace(' ', '-', strtolower($this->getTitel()));

		$settingsJson = htmlspecialchars(
			json_encode($this->getSettings(), DEBUG ? JSON_PRETTY_PRINT : 0)
		);
		$vliegendeKnoppenClass = $this->vliegendeKnoppen ? 'vliegende-knoppen' : '';

		return <<<HTML
<h2 id="table-{$id}" class="Titel {$vliegendeKnoppenClass}">{$this->getTitel()}</h2>

<table id="{$this->dataTableId}" class="ctx-datatable display" data-settings="{$settingsJson}"></table>
HTML;
	}

	public function getJavascript()
	{
		//Nothing should be returned here because the script is already embedded in getView
		return '';
	}
}

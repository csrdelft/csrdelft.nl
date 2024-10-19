<?php

namespace CsrDelft\Component\DataTable;

use CsrDelft\common\Doctrine\Type\DateTimeImmutableType;
use CsrDelft\common\Util\ReflectionUtil;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

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
class DataTableBuilder
{
	const POST_SELECTION = 'DataTableSelection';

	public $model;

	protected $dataUrl;
	protected $titel;
	protected $beschrijving;
	protected $dataTableId;
	protected $defaultLength = 10;
	public $selectEnabled = true;
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
	/**
	 * @var CamelCaseToSnakeCaseNameConverter
	 */
	private $camelCaseToSnakeCaseNameConverter;

	public function __construct(
		private readonly SerializerInterface $serializer,
		private readonly NormalizerInterface $normalizer,
		private readonly EntityManagerInterface $entityManager
	) {
		$this->camelCaseToSnakeCaseNameConverter = new CamelCaseToSnakeCaseNameConverter();
	}

	public function loadFromClass(string $className)
	{
		if (is_a($className, CustomDataTableEntry::class, true)) {
			$this->loadCustomDataTableEntry($className);
		} else {
			$this->loadFromMetadata(
				$this->entityManager->getClassMetadata($className)
			);
		}
	}

	public function loadFromMetadata(ClassMetadata $metadata)
	{
		// generate columns from entity attributes
		foreach ($metadata->getFieldNames() as $attribute) {
			$type = Type::getTypeRegistry()->get(
				$metadata->getTypeOfField($attribute)
			);
			$name = $this->camelCaseToSnakeCaseNameConverter->normalize($attribute);
			if ($type instanceof DateTimeImmutableType) {
				$this->addColumn($name, null, null, CellRender::DateTime());
			} elseif ($type instanceof BooleanType) {
				$this->addColumn($name, null, null, CellRender::Check());
			} else {
				$this->addColumn($name);
			}
		}

		// hide primary key columns
		foreach ($metadata->getIdentifierFieldNames() as $attribute) {
			$name = $this->camelCaseToSnakeCaseNameConverter->normalize($attribute);
			$this->hideColumn($name);
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
	 * @param DataTableKnop $knop
	 */
	public function addKnop(DataTableKnop $knop)
	{
		$knop->setDataTableId($this->dataTableId);
		$this->settings['userButtons'][] = $knop;
	}

	public function addRowKnop(DataTableRowKnop $knop)
	{
		$this->settings['rowButtons'][] = $knop;
	}

	public function columnPosition($name)
	{
		return array_search($name, array_keys($this->columns));
	}

	public function setOrder($names)
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
	public function addColumn(
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
			  reldate(DateUtil::getDateTime());
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
	public function deleteColumn($name)
	{
		if (isset($this->columns[$name])) {
			array_splice($this->columns, $this->columnPosition($name), 1);
		}
	}

	/**
	 * @param string $name
	 * @param bool $hide
	 */
	public function hideColumn($name, $hide = true)
	{
		if (isset($this->columns[$name])) {
			$this->columns[$name]['visible'] = !$hide;
		}
	}

	/**
	 * @param string $name
	 * @param bool $searchable
	 */
	public function searchColumn($name, $searchable = true)
	{
		if (isset($this->columns[$name])) {
			$this->columns[$name]['searchable'] = (bool) $searchable;
		}
	}

	/**
	 * @param string $name
	 * @param string $title
	 */
	public function setColumnTitle($name, $title)
	{
		if (isset($this->columns[$name])) {
			$this->columns[$name]['title'] = $title;
		}
	}

	protected function getSettings()
	{
		// set view modus: paging or scrolling
		if ($this->defaultLength > 0) {
			$this->settings['paging'] = true;
			$this->settings['pageLength'] = $this->defaultLength;
		} else {
			$this->settings['paging'] = false;
			$this->settings['dom'] = str_replace('i', '', $this->settings['dom']);
		}

		$this->settings['select'] = $this->selectEnabled;

		// set ajax url
		if ($this->dataUrl) {
			$this->settings['ajax'] = [
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
			$this->settings['columnGroup'] = ['column' => $groupByColumnPosition];
			$this->settings['orderFixed'] = [[$groupByColumnPosition, 'asc']];
		}

		if (count($this->settings['rowButtons']) > 0) {
			$this->columns['actionButtons'] = [
				'name' => 'actionButtons',
				'searchable' => false,
				'orderable' => false,
				'defaultContent' => '',
			];
		} else {
			// Client checkt of rowButtons bestaat
			unset($this->settings['rowButtons']);
		}

		// create visible columns index array and default order
		$index = 0;
		$visibleIndex = 0;
		foreach ($this->columns as $name => $def) {
			if (!isset($def['visible']) || $def['visible'] === true) {
				// default order by first visible orderable column
				if (
					!isset($this->settings['order']) &&
					!(isset($def['orderable']) && $def['orderable'] === false)
				) {
					$this->settings['order'] = [[$index, 'asc']];
				}

				$visibleIndex++;
			}
			$index++;
		}

		// translate columns index
		$this->settings['columns'] = array_values($this->columns);

		// Voeg nieuwe knoppen toe
		$this->settings['buttons'] = array_merge(
			$this->settings['userButtons'],
			$this->settings['buttons']
		);

		return $this->settings;
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
		return $this->model;
	}

	public function getType()
	{
		return ReflectionUtil::classNameZonderNamespace(static::class);
	}

	public function getTable()
	{
		return new DataTableInstance(
			$this->serializer,
			$this->normalizer,
			$this->getTitel(),
			$this->getBeschrijving(),
			$this->getDataTableId(),
			$this->getSettings()
		);
	}

	public function setTitel($titel)
	{
		$this->titel = $titel;

		if ($titel) {
			$this->settings['buttons'][1]['filename'] = $titel;
			$this->settings['buttons'][2]['filename'] = $titel;
		} else {
			unset($this->settings['buttons'][1]['filename']);
			unset($this->settings['buttons'][2]['filename']);
		}
	}

	public function setBeschrijving($beschrijving)
	{
		$this->beschrijving = $beschrijving;
	}

	public function getBeschrijving()
	{
		return $this->beschrijving;
	}

	// create group expand / collapse column
	public function addDefaultDetailsColumn()
	{
		$this->columns['details'] = [
			'name' => 'details',
			'data' => 'details',
			'title' => '',
			'type' => 'string',
			'orderable' => false,
			'searchable' => false,
			'defaultContent' => '',
		];
	}

	public function setTableId($tableId)
	{
		$this->dataTableId = $tableId;
	}

	public function setDataUrl($dataUrl)
	{
		$this->dataUrl = $dataUrl;
	}

	/**
	 * @param string|CustomDataTableEntry $className
	 */
	private function loadCustomDataTableEntry(string $className)
	{
		foreach ($className::getFieldNames() as $attribute) {
			$this->addColumn($attribute);
		}

		foreach ($className::getIdentifierFieldNames() as $attribute) {
			$this->hideColumn($attribute);
		}
	}

	public function resetButtons()
	{
		$this->settings['buttons'] = [];
	}
}

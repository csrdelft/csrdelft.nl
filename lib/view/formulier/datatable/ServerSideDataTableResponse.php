<?php

namespace CsrDelft\view\formulier\datatable;

use CsrDelft\model\ProfielService;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20/09/2018
 * @property PersistenceModel $model
 */
abstract class ServerSideDataTableResponse extends DataTableResponse {
	/**
	 * Draw counter.
	 *
	 * @var int
	 */
	private $draw;
	/**
	 * Paging first record indicator.
	 *
	 * @var int
	 */
	private $start;
	/**
	 * Number of records that the table can display in the current draw.
	 *
	 * @var int
	 */
	private $length;
	/**
	 * Global search value.
	 *
	 * @var string
	 */
	private $searchValue;
	/**
	 * If the global filter should be treated as a regular expression for advanced searching.
	 *
	 * @var bool
	 */
	private $searchRegex;
	/**
	 * Kolommen.
	 *
	 * @var array
	 */
	private $columns;
	/**
	 * Kolommen zoals ze van de client komen.
	 *
	 * @var array
	 */
	private $columnsRaw;
	/**
	 * Kolommen waarop gesorteerd wordt.
	 *
	 * @var array
	 */
	private $order;

	private $entries;
	private $recordsFiltered;

	public function __construct($model, int $code = 200) {
		parent::__construct($model, $code);

		// Zie https://datatables.net/manual/server-side#Sent-parameters
		$input = filter_input_array_plus(INPUT_POST, [
			'draw' => FILTER_VALIDATE_INT,
			'start' => FILTER_VALIDATE_INT,
			'length' => FILTER_VALIDATE_INT,
			'search' => [
				'filter' => [
					'value' => FILTER_SANITIZE_STRING,
					'regex' => FILTER_VALIDATE_BOOLEAN,
				],
				'flags' => FILTER_REQUIRE_ARRAY,
			],
			'order' => [
				'filter' => [
					'column' => FILTER_VALIDATE_INT,
					'dir' => FILTER_SANITIZE_STRING,
				],
				'flags' => FILTER_REQUIRE_ARRAY,
			],
			'columns' => [
				'filter' => [
					'data' => FILTER_SANITIZE_STRING,
					'name' => FILTER_SANITIZE_STRING,
					'searchable' => FILTER_VALIDATE_BOOLEAN,
					'orderable' => FILTER_VALIDATE_BOOLEAN,
					// TODO: Support column search.
				],
				'flags' => FILTER_REQUIRE_ARRAY,
			]
		]);

		$this->draw = $input['draw'];
		$this->start = $input['start'];
		$this->length = $input['length'];
		$this->searchValue = $input['search']['value'];
		$this->searchRegex = $input['search']['regex'];
		$this->columnsRaw = $input['columns'];
		$this->order = $input['order'];

		$this->columns = $this->columnsRaw;

		$zoekKolommen = array_filter($this->columns, function ($column) {
			return $column['searchable'];
		});

		$orderKolommen = array_map(function ($column) {
			return ['data' => $this->columns[$column['column']]['data'], 'dir' => $column['dir'] == 'asc' ? 'ASC' : 'DESC'];
		}, $this->order);

		$criteria = join(" OR ", array_map(function ($column) {
			return "${column['data']} LIKE :searchTerm";
		}, $zoekKolommen));

		$orderBy = join(", ", array_map(function ($column) {
			$columnName = $column['data'];

			if (isset($this->getOrderAlias()[$columnName])) {
				$columnName = $this->getOrderAlias()[$columnName];
			}

			return "$columnName ${column['dir']}";
		}, $orderKolommen));

		$this->recordsFiltered = $this->count($this->searchValue);// $this->model->count($criteria, [':searchTerm' => sql_contains($this->searchValue)]);
		$this->entries = $this->find($this->searchValue, $this->filterColumns($this->columns), $orderBy, $this->length, $this->start);
	}

	/**
	 * Probeer alleen te zoeken op kolommen die ook in de database bestaan.
	 * Zorg dat de index blijft kloppen.
	 * @param array $columns
	 * @return array
	 */
	protected function filterColumns($columns) {
		$persistentAttributes = $this->model->getAttributes();

		$ret = [];

		foreach ($columns as $index => $column) {
			if (in_array($column['data'], $persistentAttributes)) {
				$ret[$index] = $column;
			}
		}

		return $ret;
	}

	protected abstract function count($zoekFilter);

	protected abstract function find($zoekWaarde, $kolommen, $oderBy, $length, $start);

	protected function getOrderAlias() {
		return [];
	}

	protected function toArray($entity) {
		return $entity;
	}

	public function view() {
		http_response_code($this->code);
		header('Content-Type: application/json');

		echo json_encode([
			"draw" => $this->draw,
			"recordsTotal" => $this->model->find()->rowCount(),
			"recordsFiltered" => $this->recordsFiltered,
			"modal" => $this->modal,
			"autoUpdate" => $this->autoUpdate,
			"lastUpdate" => time() - 1,
			"data" => array_map(function ($entity) {
				return $this->toArray($entity);
			}, $this->entries)
		]);
	}
}

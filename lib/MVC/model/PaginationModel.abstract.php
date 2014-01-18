<?php

require_once 'MVC/model/PersistenceModel.abstract.php';

/**
 * PaginationModel.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Provides pagination on top of the persistence model.
 * 
 */
abstract class PaginationModel extends PersistenceModel {

	/**
	 * Starts at 0
	 * @var int
	 */
	private $current_page_number;
	protected $per_page;
	protected $last_page_number;
	protected $where;
	protected $where_params;
	protected $orderby;

	protected function __construct(PersistentEntity $orm_entity, $per_page = 25, $where = null, array $where_params = array(), $orderby = null) {
		parent::__construct($orm_entity);
		$this->current_page_number = 0;
		$this->per_page = $per_page;
		$this->where = $where;
		$this->where_params = $where_params;
		$this->orderby = $orderby;
	}

	/**
	 * Save current page to session variable to remember where the user was.
	 * 
	 * @param PersistentEntity $entity
	 */
	protected function saveCurrentPage(PersistentEntity $entity) {
		$id = 'current_page_number_' . get_class($entity) . implode('_', $entity->getValues(true));
		$_SESSION[$id] = $this->current_page_number;
	}

	/**
	 * Not nessecarily the same class as the orm entity.
	 * Example: forum topic (this entity) has paging of forum posts (orm entity).
	 * 
	 * @param PersistentEntity $entity
	 */
	protected function loadCurrentPage(PersistentEntity $entity) {
		$id = 'current_page_number_' . get_class($entity) . implode('_', $entity->getValues(true));
		if (array_key_exists($id, $_SESSION) && $this->hasPage($_SESSION[$id])) {
			$this->current_page_number = $_SESSION[$id];
		}
	}

	/**
	 * Starts at 0.
	 * 
	 * @return int
	 */
	public function getPageNumber() {
		return $this->current_page_number;
	}

	/**
	 * Get the current page or a specific page that has to exist.
	 * 
	 * @param type $number
	 * @return type
	 */
	public function getPage($number = null) {
		if (is_int($number) && $this->hasPage($number)) {
			$this->current_page_number = $number;
		}
		return $this->find($this->where, $this->where_params, $this->orderby, $this->per_page, $this->current_page_number * $this->per_page);
	}

	public function getPageCount() {
		if (!isset($this->last_page_number)) {
			$this->recount();
		}
		return $this->last_page_number;
	}

	/**
	 * Calculate amount of pages based on total amount.
	 * 
	 * @param int $total
	 */
	public function setPageCount($total) {
		$this->last_page_number = ceil($total / $this->per_page);
	}

	public function recount() {
		$sql = 'SELECT COUNT(*) as total FROM ' . $this->orm_entity->getTableName();
		if ($this->where !== null) {
			$sql .= ' WHERE ' . $this->where;
		}
		$query = Database::instance()->prepare($sql, $this->where_params);
		$query->execute($this->where_params);
		setPageCount((int) $query->fetchColumn());
	}

	/**
	 * Start at 0
	 * 
	 * @param int $number
	 * @return boolean
	 */
	public function hasPage($number) {
		return $number <= $this->getPageCount();
	}

	public function nextPage() {
		if ($this->hasNextPage()) {
			$this->current_page_number++;
		}
		return $this->getPage();
	}

	public function hasNextPage() {
		return $this->hasPage($this->current_page_number + 1);
	}

	public function previousPage() {
		if ($this->hasPreviousPage()) {
			$this->current_page_number--;
		}
		return $this->getPage();
	}

	public function hasPreviousPage() {
		return $this->hasPage($this->current_page_number - 1);
	}

}

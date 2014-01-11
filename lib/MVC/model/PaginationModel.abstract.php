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
	 * Start at 0
	 * 
	 * @var int
	 */
	protected $current_page_number;
	protected $per_page;
	private $last_page_number;
	private $where;
	private $values;
	private $orderby;
	private $assoc;

	public function setPaging($per_page, $where = null, array $values = array(), $orderby = null, $assoc = false) {
		$this->per_page = $per_page;
		$this->where = $where;
		$this->values = $values;
		$this->orderby = $orderby;
		$this->assoc = $assoc;
	}

	/**
	 * Start at 0
	 * 
	 * @return int
	 */
	public function getPageNumber() {
		return $this->current_page_number;
	}

	public function getPage($number = null) {
		if (is_int($number)) {
			$this->current_page_number = $number;
		}
		if (hasPage($this->current_page_number)) {
			return $this->select($this->where, $this->values, $this->orderby, $this->per_page, $this->current_page_number * $this->per_page);
		}
	}

	public function getPageCount() {
		if (!isset($this->last_page_number)) {
			$sql = 'SELECT COUNT(*) as total FROM ' . $this->table_name;
			if ($this->where !== null) {
				$sql .= ' WHERE ' . $this->where;
			}
			if ($this->orderby !== null) {
				$sql .= ' ORDER BY ' . $this->orderby;
			}
			$db = Database::instance();
			$query = $db->prepare($sql, $this->values);
			$query->execute($this->values);
			$this->last_page_number = ceil(((int) $query->fetchColumn()) / $this->per_page);
		}
		return $this->last_page_number;
	}

	/**
	 * Start at 0
	 * 
	 * @param int $number
	 * @return boolean
	 */
	public function hasPage($number) {
		return $number <= getPageCount();
	}

	public function nextPage() {
		if ($this->hasNextPage()) {
			$this->current_page_number++;
		}
		return getPage();
	}

	public function hasNextPage() {
		return $this->hasPage($this->current_page_number + 1);
	}

	public function previousPage() {
		if ($this->hasPreviousPage()) {
			$this->current_page_number--;
		}
		return getPage();
	}

	public function hasPreviousPage() {
		return $this->hasPage($this->current_page_number - 1);
	}

}

?>
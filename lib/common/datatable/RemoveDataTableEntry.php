<?php


namespace CsrDelft\common\datatable;


class RemoveDataTableEntry {
	private $entity;

	public function __construct($entity) {
		$this->entity = $entity;
	}

	/**
	 * @return mixed
	 */
	public function getEntity() {
		return $this->entity;
	}
}

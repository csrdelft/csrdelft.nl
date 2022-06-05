<?php

namespace CsrDelft\Component\DataTable;

class RemoveDataTableEntry
{
	private $id;
	private $class;

	public function __construct($id, $class)
	{
		$this->id = $id;
		$this->class = $class;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getClass()
	{
		return $this->class;
	}
}

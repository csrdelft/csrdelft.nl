<?php

namespace CsrDelft\Component\DataTable;

class RemoveDataTableEntry
{
	public function __construct(private $id, private $class)
	{
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

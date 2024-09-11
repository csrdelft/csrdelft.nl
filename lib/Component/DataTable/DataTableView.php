<?php

namespace CsrDelft\Component\DataTable;

class DataTableView implements \Stringable
{
	public function __toString(): string
	{
		return $this->data;
	}

	/**
	 * @param string $data
	 */
	public function __construct(private $data)
	{
	}
}

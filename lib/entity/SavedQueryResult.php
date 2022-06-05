<?php

namespace CsrDelft\entity;

class SavedQueryResult
{
	/** @var SavedQuery */
	public $query;
	/** @var string[] */
	public $cols;
	/** @var mixed[][] */
	public $rows;
	/** @var string */
	public $error;
}

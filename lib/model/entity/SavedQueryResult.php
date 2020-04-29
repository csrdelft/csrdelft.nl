<?php


namespace CsrDelft\model\entity;


use CsrDelft\entity\SavedQuery;

class SavedQueryResult {
	/** @var SavedQuery */
	public $query;
	/** @var string[] */
	public $cols;
	/** @var mixed[][] */
	public $rows;
	/** @var string */
	public $error;
}

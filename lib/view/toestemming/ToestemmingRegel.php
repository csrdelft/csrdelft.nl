<?php


namespace CsrDelft\view\toestemming;


class ToestemmingRegel
{
	/** @var string */
	public $module;
	/** @var string */
	public $id;
	/** @var string */
	public $type;
	/** @var string */
	public $opties;
	/** @var string */
	public $label;
	/** @var string */
	public $waarde;
	/** @var string */
	public $default;

	public function __construct(
		string $module,
		string $id,
		string $type,
		string $opties,
		string $label,
		string $waarde,
		string $default
	)
	{
		$this->module = $module;
		$this->id = $id;
		$this->type = $type;
		$this->opties = $opties;
		$this->label = $label;
		$this->waarde = $waarde;
		$this->default = $default;
	}
}

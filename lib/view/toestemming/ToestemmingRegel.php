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

	public function __construct($module, $id, $type, $opties, $label, $waarde, $default)
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

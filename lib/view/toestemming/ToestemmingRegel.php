<?php


namespace CsrDelft\view\toestemming;


class ToestemmingRegel {
	public $module;
	public $id;
	public $type;
	public $opties;
	public $label;
	public $waarde;
	public $default;

	public function __construct($module, $id, $type, $opties, $label, $waarde, $default) {
		$this->module = $module;
		$this->id = $id;
		$this->type = $type;
		$this->opties = $opties;
		$this->label = $label;
		$this->waarde = $waarde;
		$this->default = $default;
	}
}

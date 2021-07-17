<?php


namespace CsrDelft\view\formulier\invoervelden;


use CsrDelft\entity\profiel\Profiel;

class ProfielEntityField extends DoctrineEntityField {
	public $zoekin;

	public function __construct($name, $value, $description, $zoekin) {
		parent::__construct($name, $value, $description, Profiel::class, '');

		$this->zoekin = $zoekin;

		$this->suggestieIdField = 'uid';
	}

	public function getUrl(): string
	{
		return '/tools/naamsuggesties?zoekin=' . $this->zoekin . '&q=';
	}
}

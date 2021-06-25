<?php


namespace CsrDelft\view\formulier\invoervelden;


use CsrDelft\entity\profiel\Profiel;

class ProfielEntityField extends DoctrineEntityField {
	public function __construct($name, $value, $description, $zoekin) {
		parent::__construct($name, $value, $description, Profiel::class, '/tools/naamsuggesties?zoekin=' . $zoekin . '&q=');

		$this->suggestieIdField = 'uid';
	}
}

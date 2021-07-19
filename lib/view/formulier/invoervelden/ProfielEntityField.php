<?php


namespace CsrDelft\view\formulier\invoervelden;


use CsrDelft\entity\profiel\Profiel;

/**
 * Class ProfielEntityField
 * @package CsrDelft\view\formulier\invoervelden
 * @method Profiel|null getFormattedValue()
 */
class ProfielEntityField extends DoctrineEntityField {
	public $zoekin;

	public function __construct($name, $value, $description, $zoekin = 'alleleden') {
		parent::__construct($name, $value, $description, Profiel::class, '');

		$this->zoekin = $zoekin;

		$this->suggestieIdField = 'uid';
	}

	public function getUrl(): string
	{
		return '/tools/naamsuggesties?zoekin=' . $this->zoekin . '&q=';
	}
}

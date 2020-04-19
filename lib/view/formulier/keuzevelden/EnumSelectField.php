<?php


namespace CsrDelft\view\formulier\keuzevelden;


use CsrDelft\common\Enum;

class EnumSelectField extends SelectField {
	/**
	 * @var Enum
	 */
	private $enumClass;

	/**
	 * EnumSelectField constructor.
	 * @param $name
	 * @param $value Enum
	 * @param $description
	 * @param int $size
	 * @param bool $multiple
	 */
	public function __construct($name, $value, $description, $size = 1, $multiple = false) {
		parent::__construct($name, $value->getValue(), $description, $value::getEnumDescriptions(), false, $size, $multiple);

		$this->enumClass = get_class($value);
	}

	public function getFormattedValue() {
		$enumClass = $this->enumClass;
		return $enumClass::from($this->value);
	}
}

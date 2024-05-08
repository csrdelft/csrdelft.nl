<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\common\Enum;

class EnumSelectField extends SelectField
{
	/**
	 * @var Enum
	 */
	private $enumClass;

	/**
	 * EnumSelectField constructor.
	 * @param $name
	 * @param $value Enum
	 * @param $description
	 * @param $enumClass Enum|string
	 * @param int $size
	 * @param bool $multiple
	 */
	public function __construct(
		$name,
		$value,
		$description,
		$enumClass,
		$size = 1,
		$multiple = false
	) {
		parent::__construct(
			$name,
			$value == null ? null : $value->getValue(),
			$description,
			$enumClass::getEnumDescriptions(),
			$size,
			$multiple
		);

		$this->enumClass = $enumClass;
	}

	public function getFormattedValue()
	{
		if ($this->value == null) {
			return null;
		}

		$enumClass = $this->enumClass;
		return $enumClass::from($this->value);
	}
}

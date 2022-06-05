<?php

namespace CsrDelft\view\formulier\keuzevelden;

use DateTimeInterface;

class TimeObjectField extends TimeField
{
	public function __construct($name, $value, $description, $minutensteps = null)
	{
		if ($value instanceof DateTimeInterface) {
			$value = date_format_intl($value, TIME_FORMAT);
		}
		parent::__construct($name, $value, $description, $minutensteps);
	}

	public function getFormattedValue()
	{
		return $this->value ? date_create_immutable($this->value) : null;
	}
}

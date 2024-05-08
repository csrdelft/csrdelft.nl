<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\common\Util\DateUtil;
use DateTimeImmutable;
use DateTimeInterface;

class TimeObjectField extends TimeField
{
	public function __construct($name, $value, $description, $minutensteps = null)
	{
		if ($value instanceof DateTimeInterface) {
			$value = DateUtil::dateFormatIntl($value, DateUtil::TIME_FORMAT);
		}
		parent::__construct($name, $value, $description, $minutensteps);
	}

	public function getFormattedValue(): DateTimeImmutable|false|null
	{
		return $this->value ? date_create_immutable($this->value) : null;
	}
}

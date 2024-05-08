<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\common\Util\DateUtil;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * DateObjectField
 *
 * Een DateField die als input en output een DateTime object heeft.
 *
 */
class DateObjectField extends DateField
{
	public function __construct(
		$name,
		$value,
		$description,
		$maxyear = null,
		$minyear = null
	) {
		if ($value instanceof DateTimeInterface) {
			$value = DateUtil::dateFormatIntl($value, DateUtil::DATE_FORMAT);
		}
		parent::__construct($name, $value, $description, $maxyear, $minyear);
	}

	public function getFormattedValue(): DateTimeImmutable|false
	{
		return date_create_immutable($this->getValue());
	}
}

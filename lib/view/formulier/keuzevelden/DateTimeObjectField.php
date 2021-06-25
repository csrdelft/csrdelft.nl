<?php

namespace CsrDelft\view\formulier\keuzevelden;

use DateTimeInterface;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Date time picker with range (optional). Takes a DateTime object as input and retuns a DateTime object
 */
class DateTimeObjectField extends DateTimeField {
	public function __construct($name, $value, $description, $maxyear = null, $minyear = null) {
		if ($value instanceof DateTimeInterface) {
			$value = date_format_intl($value, DATETIME_FORMAT);
		}
		parent::__construct($name, $value, $description, $maxyear, $minyear);
	}

	public function getFormattedValue() {
		return $this->value ? date_create_immutable($this->value) : null;
	}
}

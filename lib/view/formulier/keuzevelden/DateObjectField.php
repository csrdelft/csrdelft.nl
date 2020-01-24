<?php

namespace CsrDelft\view\formulier\keuzevelden;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * DateObjectField
 *
 * Een DateField die als input en output een DateTime object heeft.
 *
 */
class DateObjectField extends DateField {
	public function __construct($name, $value, $description, $maxyear = null, $minyear = null) {
		if ($value instanceof \DateTime) {
			$value = $value->format(DATE_FORMAT);
		}
		parent::__construct($name, $value, $description);
	}

	public function getFormattedValue() {
		return date_create($this->getValue());
	}
}

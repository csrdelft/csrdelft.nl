<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\invoervelden\TextField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Date time picker with range (optional). Takes a DateTime object as input and retuns a DateTime object
 */
class DateTimeObjectField extends DateTimeField {
	public function __construct($name, $value, $description, $maxyear = null, $minyear = null) {
		if ($value instanceof \DateTime) {
			$value = $value->format(DATETIME_FORMAT);
		}
		parent::__construct($name, $value, $description, $maxyear, $minyear);
	}

	public function getFormattedValue() {
		return date_create($this->value);
	}
}

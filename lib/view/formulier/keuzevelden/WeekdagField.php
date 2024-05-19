<?php

namespace CsrDelft\view\formulier\keuzevelden;
/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Dag van de week
 */
class WeekdagField extends SelectField
{
	private static $dagnamen = [
		'zondag',
		'maandag',
		'dinsdag',
		'woensdag',
		'donderdag',
		'vrijdag',
		'zaterdag',
	];

	public function __construct($name, $value, $description)
	{
		parent::__construct($name, $value, $description, self::$dagnamen);
	}

	public function getValue(): int
	{
		$this->value = parent::getValue();
		return (int) $this->value;
	}
}

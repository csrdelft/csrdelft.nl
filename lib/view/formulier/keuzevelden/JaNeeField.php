<?php

namespace CsrDelft\view\formulier\keuzevelden;
/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Ja (1) of Nee (0)
 */
class JaNeeField extends RadioField
{

	public function __construct($name, $value, $description)
	{
		parent::__construct($name, (int)$value, $description, array(1 => 'Ja', 0 => 'Nee'));
	}

	public function validate()
	{
		return array_key_exists($this->value, $this->options);
	}

	public function getValue()
	{
		// Override $this->value, want parent doet dat ook.
		$this->value = (int)parent::getValue();
		return $this->value;
	}

}

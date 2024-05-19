<?php

namespace CsrDelft\view\formulier\invoervelden;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class WachtwoordField extends TextField
{
	public $type = 'password';

	// Override TextField getValue as passwords do not need to be sanitised here
	public function getValue(): ?string
	{
		if ($this->isPosted()) {
			$this->value = $_POST[$this->name];
		} else {
			$this->value = null;
		}
		if ($this->empty_null and $this->value == '') {
			return null;
		}
		return $this->value;
	}
}

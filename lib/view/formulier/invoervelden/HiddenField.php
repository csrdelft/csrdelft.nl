<?php

namespace CsrDelft\view\formulier\invoervelden;

/**
 * @author sander
 * @since 29-06-2018
 */
class HiddenField extends InputField
{
	public function __construct($name, $value, $model = null)
	{
		parent::__construct($name, $value, null, $model);
		$this->type = 'hidden';
	}

	public function __toString(): string
	{
		return (string) $this->getHtml();
	}
}

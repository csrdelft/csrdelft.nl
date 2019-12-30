<?php


namespace CsrDelft\view\formulier\invoervelden;


use CsrDelft\common\CsrToegangException;

class SpamTrapField extends InputField {
	public function __construct($name) {
		parent::__construct($name, '', '', null);
		$this->css_classes[] = 'verborgen';
	}

	public function getValue() {
		if (parent::getValue() != '') {
			throw new CsrToegangException();
		}
	}
}

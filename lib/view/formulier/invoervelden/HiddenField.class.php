<?php
/**
 * Created by PhpStorm.
 * User: sander
 * Date: 29-6-18
 * Time: 14:54
 */

namespace CsrDelft\view\formulier\invoervelden;


class HiddenField extends InputField {

	public function __construct($name, $value, $model = null) {
		parent::__construct($name, $value, null, $model);
		$this->type = "hidden";
	}

}
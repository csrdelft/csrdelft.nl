<?php

namespace CsrDelft\view\login;

use CsrDelft\model\entity\security\RememberLogin;

class RememberAfterLoginForm extends RememberLoginForm {

	public function __construct(RememberLogin $remember) {
		parent::__construct($remember);
		$this->dataTableId = false; // same as parent but without data table
	}

}

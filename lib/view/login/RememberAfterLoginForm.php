<?php

namespace CsrDelft\view\login;

use CsrDelft\model\entity\security\RememberLogin;
use CsrDelft\view\formulier\invoervelden\HiddenField;

class RememberAfterLoginForm extends RememberLoginForm {

	public function __construct(RememberLogin $remember, string $redirectUri) {
		parent::__construct($remember);
		$this->dataTableId = false; // same as parent but without data table
		$this->addFields([new HiddenField('redirect', $redirectUri)]);
	}

}

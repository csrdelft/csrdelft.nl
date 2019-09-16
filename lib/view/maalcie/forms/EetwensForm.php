<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\model\maalcie\CorveeVoorkeurenModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\invoervelden\TextareaField;

/**
 * EetwensForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor het invoeren van een eetwens.
 *
 */
class EetwensForm extends InlineForm {

	public function __construct() {

		$field = new TextareaField('eetwens', CorveeVoorkeurenModel::instance()->getEetwens(LoginModel::getProfiel()), 'Allergie/diëet:');

		parent::__construct(null, '/corvee/eetwens', $field, true, true);

		$this->formId = 'eetwens-form';
	}

}

<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\service\security\LoginService;
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
class EetwensForm extends InlineForm
{
	public function __construct()
	{
		$field = new TextareaField(
			'eetwens',
			LoginService::getProfiel()->eetwens,
			'Allergie/diëet:'
		);

		parent::__construct(null, '/corvee/voorkeuren/eetwens', $field, true, true);

		$this->formId = 'eetwens-form';
	}
}

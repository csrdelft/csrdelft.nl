<?php

/**
 * EetwensForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor het invoeren van een eetwens.
 * 
 */
class EetwensForm extends InlineForm {

	public function __construct() {
		parent::__construct(null, maalcieUrl . '/eetwens', true, true);
		$this->formId = 'eetwens-form';
		$this->field = new TextareaField('eetwens', CorveeVoorkeurenModel::getEetwens(LoginModel::getProfiel()), 'Allergie/diëet:');
	}

}

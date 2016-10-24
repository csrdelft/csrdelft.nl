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

		$field = new TextareaField('eetwens', CorveeVoorkeurenModel::getEetwens(LoginModel::getProfiel()), 'Allergie/diëet:');

		parent::__construct(null, maalcieUrl . '/eetwens', $field, true, true);
	}

	public function getFormId() {
		return 'eetwens-form';
	}

}

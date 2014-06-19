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
		parent::__construct(null, 'eetwens-form', Instellingen::get('taken', 'url') . '/eetwens', new AutoresizeTextareaField('eetwens', VoorkeurenModel::getEetwens(LoginLid::instance()->getLid()), 'Allergie/diëet:'), true);
	}

}

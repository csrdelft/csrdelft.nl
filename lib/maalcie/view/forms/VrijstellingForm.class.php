<?php

/**
 * VrijstellingForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken vrijstelling.
 * 
 */
class VrijstellingForm extends ModalForm {

	public function __construct($uid = null, $begin = null, $eind = null, $percentage = null) {
		parent::__construct(null, 'maalcie-vrijstelling-form', maalcieUrl . '/opslaan' . ($uid === null ? '' : '/' . $uid));

		if ($uid === null) {
			$this->titel = 'Vrijstelling aanmaken';
		} else {
			$this->titel = 'Vrijstelling wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields[] = new RequiredLidField('uid', $uid, 'Naam of lidnummer');
		$fields[] = new DatumField('begin_datum', $begin, 'Vanaf', date('Y') + 1, date('Y'));
		$fields[] = new DatumField('eind_datum', $eind, 'Tot en met', date('Y') + 1, date('Y'));
		$fields[] = new IntField('percentage', $percentage, 'Percentage (%)', Instellingen::get('corvee', 'vrijstelling_percentage_min'), Instellingen::get('corvee', 'vrijstelling_percentage_max'));
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

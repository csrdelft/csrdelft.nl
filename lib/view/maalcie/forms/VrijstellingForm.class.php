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
		parent::__construct(null, maalcieUrl . '/opslaan' . ($uid === null ? '' : '/' . $uid));

		if ($uid === null) {
			$this->titel = 'Vrijstelling aanmaken';
		} else {
			$this->titel = 'Vrijstelling wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields[] = new RequiredLidField('uid', $uid, 'Naam of lidnummer');
		$fields[] = new DateField('begin_datum', $begin, 'Vanaf', date('Y') + 14, date('Y'));
		$fields[] = new DateField('eind_datum', $eind, 'Tot en met', date('Y') + 14, date('Y'));
		$fields[] = new IntField('percentage', $percentage, 'Percentage (%)', 0, 100);
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

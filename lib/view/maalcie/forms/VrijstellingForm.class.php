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

	public function __construct(CorveeVrijstelling $vrijstelling) {
		parent::__construct($vrijstelling, maalcieUrl . '/opslaan' . ($vrijstelling->uid === null ? '' : '/' . $vrijstelling->uid));

		if ($vrijstelling->uid === null) {
			$this->titel = 'Vrijstelling aanmaken';
		} else {
			$this->titel = 'Vrijstelling wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields[] = new RequiredLidField('uid', $vrijstelling->uid, 'Naam of lidnummer');
		$fields[] = new DateField('begin_datum', $vrijstelling->begin_datum, 'Vanaf', date('Y') + 14, date('Y'));
		$fields[] = new DateField('eind_datum', $vrijstelling->eind_datum, 'Tot en met', date('Y') + 14, date('Y'));
		$fields[] = new IntField('percentage', $vrijstelling->percentage, 'Percentage (%)', 0, 100);
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

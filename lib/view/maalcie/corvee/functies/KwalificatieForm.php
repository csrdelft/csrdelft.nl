<?php

namespace CsrDelft\view\maalcie\corvee\functies;

use CsrDelft\model\entity\maalcie\CorveeKwalificatie;
use CsrDelft\view\formulier\invoervelden\LidField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Formulier voor het toewijzen van een corvee-kwalificatie.
 */
class KwalificatieForm extends ModalForm {

	public function __construct(CorveeKwalificatie $kwalificatie) {
		parent::__construct($kwalificatie, maalcieUrl . '/kwalificeer/' . $kwalificatie->functie_id);
		$this->titel = 'Kwalificatie toewijzen';
		$this->css_classes[] = 'PreventUnchanged';

		$fields[] = new LidField('uid', $kwalificatie->uid, 'Naam of lidnummer', 'leden');
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

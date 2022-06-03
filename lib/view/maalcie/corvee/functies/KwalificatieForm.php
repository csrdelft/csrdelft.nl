<?php

namespace CsrDelft\view\maalcie\corvee\functies;

use CsrDelft\entity\corvee\CorveeKwalificatie;
use CsrDelft\view\formulier\invoervelden\LidObjectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Formulier voor het toewijzen van een corvee-kwalificatie.
 */
class KwalificatieForm extends ModalForm
{

	public function __construct(CorveeKwalificatie $kwalificatie)
	{
		parent::__construct($kwalificatie, '/corvee/functies/kwalificeer/' . $kwalificatie->corveeFunctie->functie_id);
		$this->titel = 'Kwalificatie toewijzen';
		$this->css_classes[] = 'PreventUnchanged';

		$fields = [];
		$fields[] = new LidObjectField('profiel', $kwalificatie->profiel, 'Naam of lidnummer', 'leden');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}

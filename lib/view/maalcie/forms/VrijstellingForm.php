<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\entity\corvee\CorveeVrijstelling;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidObjectField;
use CsrDelft\view\formulier\keuzevelden\DateObjectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * VrijstellingForm.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken vrijstelling.
 *
 * @method CorveeVrijstelling getModel()
 */
class VrijstellingForm extends ModalForm {

	public function __construct(CorveeVrijstelling $vrijstelling) {
		parent::__construct($vrijstelling, '/corvee/vrijstellingen/opslaan' . ($vrijstelling->uid === null ? '' : '/' . $vrijstelling->uid));

		if ($vrijstelling->uid === null) {
			$this->titel = 'Vrijstelling aanmaken';
		} else {
			$this->titel = 'Vrijstelling wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields = [];
		$fields[] = new RequiredLidObjectField('profiel', $vrijstelling->profiel, 'Naam of lidnummer');
		$fields[] = new DateObjectField('begin_datum', $vrijstelling->begin_datum, 'Vanaf', date('Y') + 14, date('Y'));
		$fields[] = new DateObjectField('eind_datum', $vrijstelling->eind_datum, 'Tot en met', date('Y') + 14, date('Y'));
		$fields[] = new IntField('percentage', $vrijstelling->percentage, 'Percentage (%)', 0, 100);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}

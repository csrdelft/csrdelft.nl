<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\entity\bibliotheek\BoekRecensie;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

/**
 * Boek weergeven
 */
class RecensieFormulier extends Formulier {

	public $formulier;
	public function __construct(BoekRecensie $recensie) {
		parent::__construct($recensie, "/bibliotheek/boek/$recensie->boek_id/recensie", '');
		$fields = [];
		$fields['beschrijving'] = new TextareaField("beschrijving", $recensie->beschrijving, null);

		$fields[] = new SubmitKnop();
		$this->addFields($fields);
		$this->css_classes[] = 'boekformulier';

	}

	public function getModel() {
		return $this->model;
	}

	public function isNieuw() {
		return $this->model->id == null;
	}

}

<?php


namespace CsrDelft\view\bibliotheek;


use CsrDelft\entity\bibliotheek\BoekExemplaar;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

class BoekExemplaarFormulier extends Formulier {

	/**
	 * BoekExemplaarForm constructor.
	 * @param BoekExemplaar $exemplaar
	 */
	public function __construct(BoekExemplaar $exemplaar) {
		parent::__construct($exemplaar, "/bibliotheek/exemplaar/$exemplaar->id", '');
		$fields = [];
		$fields[] = new TextField('opmerking', $exemplaar->opmerking, "Beschrijving:");
		$fields[] = new SubmitKnop();
		$this->addFields($fields);
	}
}

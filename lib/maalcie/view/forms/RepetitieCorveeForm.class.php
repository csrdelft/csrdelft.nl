<?php

/**
 * RepetitieCorveeForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor nieuw periodiek corvee.
 * 
 */
class RepetitieCorveeForm extends ModalForm {

	public function __construct(CorveeRepetitie $repetitie, $beginDatum = null, $eindDatum = null, $mid = null) {
		parent::__construct($mid, 'maalcie-repetitie-aanmaken-form', maalcieUrl . '/aanmaken/' . $repetitie->getCorveeRepetitieId());
		$this->titel = 'Periodiek corvee aanmaken';

		$fields[] = new HtmlComment('<p>Aanmaken op de eerste ' . $repetitie->getDagVanDeWeekText() . 'en vervolgens ' . $repetitie->getPeriodeInDagenText() . ' in de periode:</p>');
		$fields['begin'] = new DatumField('begindatum', $beginDatum, 'Vanaf', date('Y') + 1, date('Y'));
		$fields['eind'] = new DatumField('einddatum', $eindDatum, 'Tot en met', date('Y') + 1, date('Y'));
		$fields['mid'] = new IntField('maaltijd_id', $mid, null);
		$fields['mid']->hidden = true;
		$fields['mid']->locked = true;
		$fields[] = new FormKnoppen();

		$this->addFields($fields);
	}

	public function validate() {
		$valid = parent::validate();
		$fields = $this->getFields();
		if (strtotime($fields['eind']->getValue()) < strtotime($fields['begin']->getValue())) {
			$fields['eind']->error = 'Moet na begindatum liggen';
			$valid = false;
		}
		return $valid;
	}

}

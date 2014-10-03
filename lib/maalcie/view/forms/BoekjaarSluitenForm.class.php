<?php

/**
 * BoekjaarSluitenForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor het sluiten van het MaalCie-boekjaar.
 * 
 */
class BoekjaarSluitenForm extends ModalForm {

	public function __construct($beginDatum = null, $eindDatum = null) {
		parent::__construct(null, 'maalcie-boekjaar-sluiten-form', Instellingen::get('taken', 'url') . '/sluitboekjaar');
		$this->titel = 'Boekjaar sluiten';

		$fields[] = new HtmlComment('<p class="error">Dit is een onomkeerbare stap!</p>');
		$fields['begin'] = new DatumField('begindatum', $beginDatum, 'Vanaf', date('Y') + 1, date('Y') - 2);
		$fields['eind'] = new DatumField('einddatum', $eindDatum, 'Tot en met', date('Y') + 1, date('Y') - 2);
		$fields[] = new FormButtons();

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

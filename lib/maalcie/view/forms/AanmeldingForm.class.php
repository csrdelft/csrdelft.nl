<?php

/**
 * AanmeldingForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te verwijderen maaltijd-aanmelding.
 * 
 */
class AanmeldingForm extends ModalForm {

	public function __construct($mid, $nieuw, $uid = null, $gasten = 0) {
		parent::__construct(null, 'maalcie-aanmelding-form', Instellingen::get('taken', 'url') . '/ander' . ($nieuw ? 'aanmelden' : 'afmelden') . '/' . $mid);

		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('invalid mid');
		}
		if ($nieuw) {
			$this->title = 'Aanmelding toevoegen/aanpassen';
		} else {
			$this->title = 'Aanmelding verwijderen (inclusief gasten)';
		}
		$this->css_classes[] = 'PreventUnchanged';

		$fields[] = new LidField('voor_lid', $uid, 'Naam of lidnummer', 'leden');
		if ($nieuw) {
			$fields[] = new IntField('aantal_gasten', $gasten, 'Aantal gasten', 0, 200);
		}
		$fields[] = new FormButtons();

		$this->addFields($fields);
	}

}

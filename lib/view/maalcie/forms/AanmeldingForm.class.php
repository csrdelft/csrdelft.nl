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

	/**
	 * AanmeldingForm constructor.
	 * @param Maaltijd $maaltijd
	 * @param boolean $nieuw
	 * @param string $uid
	 * @param int $gasten
	 */
	public function __construct(Maaltijd $maaltijd, $nieuw, $uid = null, $gasten = 0) {
		parent::__construct(null, '/maaltijden/beheer/' . ($nieuw ? 'aanmelden' : 'afmelden'), true, true);

		if ($nieuw) {
			$this->titel = 'Aanmelding toevoegen/aanpassen';
		} else {
			$this->titel = 'Aanmelding verwijderen (inclusief gasten)';
		}
		$this->css_classes[] = 'PreventUnchanged';

		$fields[] = new LidField('voor_lid', $uid, 'Naam of lidnummer', 'leden');
		if ($nieuw) {
			$fields[] = new IntField('aantal_gasten', $gasten, 'Aantal gasten', 0, 200);
		}
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

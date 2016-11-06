<?php

/**
 * MaaltijdForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken maaltijd.
 * 
 */
class MaaltijdForm extends ModalForm {

	public function __construct(Maaltijd $maaltijd) {
		parent::__construct($maaltijd, maalcieUrl . '/opslaan/' . $maaltijd->maaltijd_id);
        
		if ($maaltijd->maaltijd_id < 0) {
			throw new Exception('invalid mid');
		}
		if ($maaltijd->maaltijd_id == 0) {
			$this->titel = 'Maaltijd aanmaken';
		} else {
			$this->titel = 'Maaltijd wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields['mrid'] = new IntField('mlt_repetitie_id', $maaltijd->maaltijd_id, null);
		$fields['mrid']->readonly = true;
		$fields['mrid']->hidden = true;
		$fields[] = new RequiredTextField('titel', $maaltijd->titel, 'Titel', 255, 5);
		$fields[] = new RequiredDateField('datum', $maaltijd->datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$fields[] = new RequiredTimeField('tijd', $maaltijd->tijd, 'Tijd', 15);
		$fields[] = new RequiredBedragField('prijs', $maaltijd->prijs, 'Prijs', '€', 0, 50, 0.50);
		$fields[] = new RequiredIntField('aanmeld_limiet', $maaltijd->aanmeld_limiet, 'Aanmeldlimiet', 0, 200);
		$fields[] = new RechtenField('aanmeld_filter', $maaltijd->aanmeld_filter, 'Aanmeldrestrictie');
		$fields[] = new BBCodeField('omschrijving', $maaltijd->omschrijving, 'Omschrijving');
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}

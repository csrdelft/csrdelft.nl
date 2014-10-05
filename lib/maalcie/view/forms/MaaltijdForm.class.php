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

	public function __construct($mid, $mrid = null, $titel = null, $limiet = null, $datum = null, $tijd = null, $prijs = null, $filter = null) {
		parent::__construct(null, 'maalcie-maaltijd-form', Instellingen::get('taken', 'url') . '/opslaan/' . $mid);

		if (!is_int($mid) || $mid < 0) {
			throw new Exception('invalid mid');
		}
		if ($mid === 0) {
			$this->titel = 'Maaltijd aanmaken';
		} else {
			$this->titel = 'Maaltijd wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields['mrid'] = new IntField('mlt_repetitie_id', $mrid, null);
		$fields['mrid']->hidden = true;
		$fields[] = new TextField('titel', $titel, 'Titel', 255);
		$fields[] = new DatumField('datum', $datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$fields[] = new TijdField('tijd', $tijd, 'Tijd', 15);
		$fields[] = new FloatField('prijs', $prijs, 'Prijs (€)', 0, 50);
		$fields[] = new IntField('aanmeld_limiet', $limiet, 'Aanmeldlimiet', 0, 200);
		$fields[] = new RechtenField('aanmeld_filter', $filter, 'Aanmeldrestrictie');
		$fields[] = new FormButtons();

		$this->addFields($fields);
	}

}

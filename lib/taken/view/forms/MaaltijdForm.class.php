<?php

require_once 'verticale.class.php';
require_once 'lichting.class.php';

/**
 * MaaltijdForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken maaltijd.
 * 
 */
class MaaltijdForm extends PopupForm {

	public function __construct($mid, $mrid = null, $titel = null, $limiet = null, $datum = null, $tijd = null, $prijs = null, $filter = null) {
		parent::__construct(null, 'taken-maaltijd-form', Instellingen::get('taken', 'url') . '/opslaan/' . $mid);

		if (!is_int($mid) || $mid < 0) {
			throw new Exception('invalid mid');
		}
		if ($mid === 0) {
			$this->titel = 'Maaltijd aanmaken';
		} else {
			$this->titel = 'Maaltijd wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$suggesties = array();
		$suggesties[] = 'geslacht:m';
		$suggesties[] = 'geslacht:v';
		$verticalen = \Verticale::getNamen();
		foreach ($verticalen as $naam) {
			$suggesties[] = 'verticale:' . $naam;
		}
		$jong = \Lichting::getJongsteLichting();
		for ($jaar = $jong; $jaar > $jong - 9; $jaar--) {
			$suggesties[] = 'lichting:' . $jaar;
		}

		$fields[] = new HiddenField('mlt_repetitie_id', $mrid);
		$fields[] = new TextField('titel', $titel, 'Titel', 255);
		$fields[] = new DatumField('datum', $datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$fields[] = new TijdField('tijd', $tijd, 'Tijd', 15);
		$fields[] = new FloatField('prijs', $prijs, 'Prijs (€)', 0, 50);
		$fields[] = new IntField('aanmeld_limiet', $limiet, 'Aanmeldlimiet', 0, 200);
		$fields['filter'] = new TextField('aanmeld_filter', $filter, 'Aanmeldrestrictie', 255);
		$fields['filter']->setSuggestions($suggesties);
		$fields['filter']->required = false;
		$fields['filter']->title = 'Plaats een ! vooraan om van de restrictie een uitsluiting te maken.';
		$fields[] = new SubmitResetCancel();

		$this->addFields($fields);
	}

	public function validate() {
		$valid = parent::validate();
		$fields = $this->getFields();
		$filter = $fields['filter']->getValue();
		if (!empty($filter)) {
			if (preg_match('/\s/', $filter)) {
				$fields['filter']->error = 'Mag geen spaties bevatten';
				$valid = false;
			}
			$filter = explode(':', $filter);
			if (sizeof($filter) !== 2 || empty($filter[0]) || empty($filter[1])) {
				$fields['filter']->error = 'Ongeldige restrictie';
				$valid = false;
			}
		}
		return $valid;
	}

}

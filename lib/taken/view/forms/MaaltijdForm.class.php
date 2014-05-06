<?php

require_once 'verticale.class.php';
require_once 'lichting.class.php';

/**
 * MaaltijdForm.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken maaltijd.
 * 
 */
class MaaltijdForm extends TemplateView {

	private $_form;
	private $_mid;

	public function __construct($mid, $mrid = null, $titel = null, $limiet = null, $datum = null, $tijd = null, $prijs = null, $filter = null) {
		parent::__construct();
		$this->_mid = $mid;

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

		$this->_form = new Formulier(null, 'taken-maaltijd-form', Instellingen::get('taken', 'url') . '/opslaan/' . $mid, $fields);
	}

	public function getTitel() {
		if ($this->_mid === 0) {
			return 'Maaltijd aanmaken';
		}
		return 'Maaltijd wijzigen';
	}

	public function view() {
		$this->_form->addCssClass('popup');
		$this->_form->addCssClass('PreventUnchanged');
		$this->smarty->assign('form', $this->_form);
		if ($this->_mid === 0) {
			$this->smarty->assign('nocheck', true);
		}
		$this->smarty->display('taken/popup_form.tpl');
	}

	public function validate() {
		if (!is_int($this->_mid) || $this->_mid < 0) {
			return false;
		}
		$fields = $this->_form->getFields();
		$filter = $fields['filter']->getValue();
		if (!empty($filter)) {
			if (preg_match('/\s/', $filter)) {
				$fields['filter']->error = 'Mag geen spaties bevatten';
				return false;
			}
			$filter = explode(':', $filter);
			if (sizeof($filter) !== 2 || empty($filter[0]) || empty($filter[1])) {
				$fields['filter']->error = 'Ongeldige restrictie';
				return false;
			}
		}
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>
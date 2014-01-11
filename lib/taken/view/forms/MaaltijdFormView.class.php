<?php


require_once 'verticale.class.php';
require_once 'lichting.class.php';

/**
 * MaaltijdFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken maaltijd.
 * 
 */
class MaaltijdFormView extends TemplateView {

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

		$formFields[] = new \HiddenField('mlt_repetitie_id', $mrid);
		$formFields[] = new \InputField('titel', $titel, 'Titel', 255);
		$formFields[] = new \DatumField('datum', $datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$formFields[] = new \TijdField('tijd', $tijd, 'Tijd', 15);
		$formFields[] = new \FloatField('prijs', $prijs, 'Prijs (€)', 50, 0);
		$formFields[] = new \IntField('aanmeld_limiet', $limiet, 'Aanmeldlimiet', 200, 0);
		$formFields['filter'] = new \InputField('aanmeld_filter', $filter, 'Aanmeldrestrictie', 255, $suggesties);
		$formFields['filter']->required = false;
		$formFields['filter']->title = 'Plaats een ! vooraan om van de restrictie een uitsluiting te maken.';

		$this->_form = new \Formulier('taken-maaltijd-form', $GLOBALS['taken_module'] . '/opslaan/' . $mid, $formFields);
	}

	public function getTitel() {
		if ($this->_mid === 0) {
			return 'Maaltijd aanmaken';
		}
		return 'Maaltijd wijzigen';
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->_form->css_classes .= ' popup';
		$this->assign('form', $this->_form);
		if ($this->_mid === 0) {
			$this->assign('nocheck', true);
		}
		$this->display('taken/popup_form.tpl');
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
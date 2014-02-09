<?php

/**
 * CorveeRepetitieFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken corvee-repetitie.
 * 
 */
class CorveeRepetitieFormView extends TemplateView {

	private $_form;
	private $_crid;

	public function __construct($crid, $mrid = null, $dag = null, $periode = null, $fid = null, $punten = null, $aantal = null, $voorkeur = null, $verplaats = null) {
		parent::__construct();
		$this->_crid = $crid;

		$functieNamen = FunctiesModel::getAlleFuncties(true); // grouped by fid
		$functiePunten = 'var punten=[];';
		foreach ($functieNamen as $functie) {
			$functieNamen[$functie->getFunctieId()] = $functie->getNaam();
			$functiePunten .= 'punten[' . $functie->getFunctieId() . ']=' . $functie->getStandaardPunten() . ';';
			if ($punten === null) {
				$punten = $functie->getStandaardPunten();
			}
		}

		$mlt_repetities = MaaltijdRepetitiesModel::getAlleRepetities();
		$repetitieNamen = array('' => '');
		foreach ($mlt_repetities as $rep) {
			$repetitieNamen[$rep->getMaaltijdRepetitieId()] = $rep->getStandaardTitel();
		}

		$formFields['fid'] = new SelectField('functie_id', $fid, 'Functie', $functieNamen);
		$formFields['fid']->setOnChangeScript($functiePunten . "$('#field_standaard_punten').val(punten[this.value]);");
		$formFields[] = new WeekdagField('dag_vd_week', $dag, 'Dag v/d week');
		$formFields['dag'] = new IntField('periode_in_dagen', $periode, 'Periode (in dagen)', 183, 0);
		$formFields['dag']->title = 'Als de periode ongelijk is aan 7 is dit de start-dag bij het aanmaken van periodiek corvee';
		$formFields['vrk'] = new VinkField('voorkeurbaar', $voorkeur, 'Voorkeurbaar');
		if ($this->_crid !== 0) {
			$formFields['vrk']->setOnChangeScript("if (!this.checked) alert('Alle voorkeuren zullen worden verwijderd!');");
		}
		$formFields[] = new SelectField('mlt_repetitie_id', $mrid, 'Maaltijdrepetitie', $repetitieNamen);
		$formFields[] = new IntField('standaard_punten', $punten, 'Standaard punten', 10, 0);
		$formFields[] = new IntField('standaard_aantal', $aantal, 'Aantal corveeërs', 10, 1);
		if ($this->_crid !== 0) {
			$formFields['ver'] = new VinkField('verplaats_dag', $verplaats, 'Ook verplaatsen');
			$formFields['ver']->title = 'Verplaats naar dag v/d week bij bijwerken';
		}

		$this->_form = new Formulier('taken-corvee-repetitie-form', Instellingen::get('taken', 'url') . '/opslaan/' . $crid, $formFields);
	}

	public function getTitel() {
		if ($this->_crid === 0) {
			return 'Corveerepetitie aanmaken';
		}
		return 'Corveerepetitie wijzigen';
	}

	public function view() {
		$this->_form->css_classes[] = 'popup';
		$this->smarty->assign('form', $this->_form);
		if ($this->_crid === 0) {
			$this->smarty->assign('nocheck', true);
		} elseif ($this->_crid > 0) {
			$this->smarty->assign('bijwerken', Instellingen::get('taken', 'url') . '/bijwerken/' . $this->_crid);
		}
		$this->smarty->display('taken/popup_form.tpl');
	}

	public function validate() {
		if (!is_int($this->_crid) || $this->_crid < 0) {
			return false;
		}
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>
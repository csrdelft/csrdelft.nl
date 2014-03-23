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

		$functieNamen = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
		$functiePunten = 'var punten=[];';
		foreach ($functieNamen as $functie) {
			$functieNamen[$functie->functie_id] = $functie->naam;
			$functiePunten .= 'punten[' . $functie->functie_id . ']=' . $functie->standaard_punten . ';';
			if ($punten === null) {
				$punten = $functie->standaard_punten;
			}
		}

		$mlt_repetities = MaaltijdRepetitiesModel::getAlleRepetities();
		$repetitieNamen = array('' => '');
		foreach ($mlt_repetities as $rep) {
			$repetitieNamen[$rep->getMaaltijdRepetitieId()] = $rep->getStandaardTitel();
		}

		$fields['fid'] = new SelectField('functie_id', $fid, 'Functie', $functieNamen);
		$fields['fid']->setOnChangeScript($functiePunten . "$('#field_standaard_punten').val(punten[this.value]);");
		$fields[] = new WeekdagField('dag_vd_week', $dag, 'Dag v/d week');
		$fields['dag'] = new IntField('periode_in_dagen', $periode, 'Periode (in dagen)', 0, 183);
		$fields['dag']->title = 'Als de periode ongelijk is aan 7 is dit de start-dag bij het aanmaken van periodiek corvee';
		$fields['vrk'] = new VinkField('voorkeurbaar', $voorkeur, 'Voorkeurbaar');
		if ($this->_crid !== 0) {
			$fields['vrk']->setOnChangeScript("if (!this.checked) alert('Alle voorkeuren zullen worden verwijderd!');");
		}
		$fields[] = new SelectField('mlt_repetitie_id', $mrid, 'Maaltijdrepetitie', $repetitieNamen);
		$fields[] = new IntField('standaard_punten', $punten, 'Standaard punten', 0, 10);
		$fields[] = new IntField('standaard_aantal', $aantal, 'Aantal corveeërs', 1, 10);
		if ($this->_crid !== 0) {
			$fields['ver'] = new VinkField('verplaats_dag', $verplaats, 'Verplaatsen');
			$fields['ver']->title = 'Verplaats naar dag v/d week bij bijwerken';
			$fields['ver']->onchange = <<<JS
var txt = $('#extraButton').html();
if (this.checked) {
	txt = txt.replace('bijwerken', 'bijwerken en verplaatsen');
} else {
	txt = txt.replace(' en verplaatsen', '');
}
$('#extraButton').html(txt);
JS;
		}
		$fields['src'] = new SubmitResetCancel();
		$fields['src']->extraText = 'Alles bijwerken';
		$fields['src']->extraTitle = 'Opslaan & alle taken bijwerken';
		$fields['src']->extraIcon = 'disk_multiple';
		$fields['src']->extraUrl = Instellingen::get('taken', 'url') . '/bijwerken/' . $crid;

		$this->_form = new Formulier(null, 'taken-corvee-repetitie-form', Instellingen::get('taken', 'url') . '/opslaan/' . $crid, $fields);
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
<?php
namespace Taken\CRV;

require_once 'formulier.class.php';

/**
 * CorveeRepetitieFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken corvee-repetitie.
 * 
 */
class CorveeRepetitieFormView extends \SimpleHtml {

	private $_form;
	private $_crid;
	
	public function __construct($crid, $mrid=null, $dag=null, $periode=null, $fid=null, $aantal=null, $voorkeur=null, $verplaats=null) {
		$this->_crid = $crid;
		
		$functieNamen = FunctiesModel::getAlleFuncties(true); // grouped by fid
		foreach ($functieNamen as $functie) {
			$functieNamen[$functie->getFunctieId()] = $functie->getNaam();
		}
		
		$mlt_repetities = \Taken\MLT\MaaltijdRepetitiesModel::getAlleRepetities();
		$repetitieNamen = array('' => '');
		foreach ($mlt_repetities as $rep) {
			$repetitieNamen[$rep->getMaaltijdRepetitieId()] = $rep->getStandaardTitel();
		}
		
		$formFields[] = new \SelectField('functie_id', $fid, 'Functie', $functieNamen);
		$formFields[] = new \WeekdagField('dag_vd_week', $dag, 'Dag v/d week');
		$formFields['dag'] = new \IntField('periode_in_dagen', $periode, 'Periode (in dagen)', 183, 0);
		$formFields['dag']->title = 'Als de periode ongelijk is aan 7 is dit de start-dag bij het aanmaken van periodiek corvee';
		$formFields['vrk'] = new \VinkField('voorkeurbaar', $voorkeur, 'Voorkeurbaar');
		if ($this->_crid !== 0) {
			$formFields['vrk']->setOnChangeScript("if (!this.checked) alert('Alle voorkeuren zullen worden verwijderd!');");
		}
		$formFields[] = new \SelectField('mlt_repetitie_id', $mrid, 'Maaltijdrepetitie', $repetitieNamen);
		$formFields[] = new \IntField('standaard_aantal', $aantal, 'Aantal corveeërs', 10, 1);
		if ($this->_crid !== 0) {
			$formFields['ver'] = new \VinkField('verplaats_dag', $verplaats, 'Ook verplaatsen');
			$formFields['ver']->title = 'Verplaats naar dag v/d week bij bijwerken';
		}
		
		$this->_form = new \Formulier('taken-corvee-repetitie-form', $GLOBALS['taken_module'] .'/opslaan/'. $crid, $formFields);
	}
	
	public function getTitel() {
		if ($this->_crid === 0) {
			return 'Corveerepetitie aanmaken';
		}
		return 'Corveerepetitie wijzigen';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		if ($this->_crid === 0) {
			$smarty->assign('nieuw', true);
		}
		elseif ($this->_crid > 0) {
			$smarty->assign('bijwerken', $GLOBALS['taken_module'] .'/bijwerken/'. $this->_crid);
		}
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		if (!is_int($this->_crid) || $this->_crid < 0) {
			return false;
		}
		return $this->_form->valid(null);
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>
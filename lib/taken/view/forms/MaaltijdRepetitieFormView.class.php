<?php
namespace Taken\MLT;

require_once 'formulier.class.php';
require_once 'verticale.class.php';

/**
 * MaaltijdRepetitieFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken maaltijd-repetitie.
 * 
 */
class MaaltijdRepetitieFormView extends \SimpleHtml {

	private $_form;
	private $_mrid;
	
	public function __construct($mrid, $dag=null, $periode=null, $titel=null, $tijd=null, $prijs=null, $abo=null, $limiet=null, $filter=null, $verplaats=null) {
		$this->_mrid = $mrid;
		
		$suggesties = array();
		$suggesties[] = 'geslacht:m';
		$suggesties[] = 'geslacht:v';
		$verticalen = \Verticale::getNamen();
		foreach ($verticalen as $naam) {
			$suggesties[] = 'verticale:'. $naam;
		}
		$jong = \Lichting::getJongsteLichting();
		for ($jaar = $jong; $jaar > $jong-9; $jaar--) {
			$suggesties[] = 'lichting:'. $jaar;
		}
		
		$formFields['req'] = new \RequiredInputField('standaard_titel', $titel, 'Standaard titel', 255);
		$formFields['req']->forcenotnull = true;
		$formFields[] = new \TijdField('standaard_tijd', $tijd, 'Standaard tijd', 15);
		$formFields['dag'] = new \WeekdagField('dag_vd_week', $dag, 'Dag v/d week');
		$formFields['dag']->title = 'Als de periode ongelijk is aan 7 is dit de start-dag bij het aanmaken van periodieke maaltijden';
		$formFields[] = new \IntField('periode_in_dagen', $periode, 'Periode (in dagen)', 183, 0);
		$formFields['abo'] = new \VinkField('abonneerbaar', $abo, 'Abonneerbaar');
		if ($this->_mrid !== 0) {
			$formFields['abo']->setOnChangeScript("if (!this.checked) alert('Alle abonnementen zullen worden verwijderd!');");
		}
		$formFields[] = new \FloatField('standaard_prijs', $prijs, 'Standaard prijs (€)', 50.00, 0.00);
		$formFields[] = new \IntField('standaard_limiet', $limiet, 'Standaard limiet', 200, 0);
		$formFields['filter'] = new \InputField('abonnement_filter', $filter, 'Aanmeldrestrictie', 255, $suggesties);
		$formFields['filter']->title = 'Plaats een ! vooraan om van de restrictie een uitsluiting te maken.';
		if ($this->_mrid !== 0) {
			$formFields['ver'] = new \VinkField('verplaats_dag', $verplaats, 'Ook verplaatsen');
			$formFields['ver']->title = 'Verplaats naar dag v/d week bij bijwerken';
		}
		
		$this->_form = new \Formulier('taken-maaltijd-repetitie-form', $GLOBALS['taken_module'] .'/opslaan/'. $mrid, $formFields);
	}
	
	public function getTitel() {
		if ($this->_mrid === 0) {
			return 'Maaltijdrepetitie aanmaken';
		}
		return 'Maaltijdrepetitie wijzigen';
	}
	
	public function view() {
		$smarty = new \Smarty3CSR();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		if ($this->_mrid === 0) {
			$smarty->assign('nocheck', true);
		}
		elseif ($this->_mrid > 0) {
			$smarty->assign('bijwerken', $GLOBALS['taken_module'] .'/bijwerken/'. $this->_mrid);
		}
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		if (!is_int($this->_mrid) || $this->_mrid < 0) {
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
		return $this->_form->valid(null);
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>
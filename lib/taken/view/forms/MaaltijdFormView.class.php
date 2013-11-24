<?php
namespace Taken\MLT;

require_once 'formulier.class.php';
require_once 'verticale.class.php';
require_once 'lichting.class.php';

/**
 * MaaltijdFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken maaltijd.
 * 
 */
class MaaltijdFormView extends \SimpleHtml {

	private $_form;
	private $_mid;
	
	public function __construct($mid, $mrid=null, $titel=null, $limiet=null, $datum=null, $tijd=null, $prijs=null, $filter=null) {
		$this->_mid = $mid;
		
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
		
		$formFields[] = new \HiddenField('mlt_repetitie_id', $mrid);
		$formFields['req'] = new \RequiredInputField('titel', $titel, 'Titel', 255);
		$formFields['req']->forcenotnull = true;
		$formFields[] = new \DatumField('datum', $datum, 'Datum', date('Y')+2, date('Y')-2);
		$formFields[] = new \TijdField('tijd', $tijd, 'Tijd', 15);
		$formFields[] = new \FloatField('prijs', $prijs, 'Prijs (€)', 50, 0);
		$formFields[] = new \IntField('aanmeld_limiet', $limiet, 'Aanmeldlimiet', 200, 0);
		$formFields['filter'] = new \InputField('aanmeld_filter', $filter, 'Aanmeldrestrictie', 255, $suggesties);
		$formFields['filter']->title = 'Plaats een ! vooraan om van de restrictie een uitsluiting te maken.';
		
		$this->_form = new \Formulier('taken-maaltijd-form', $GLOBALS['taken_module'] .'/opslaan/'. $mid, $formFields);
	}
	
	public function getTitel() {
		if ($this->_mid === 0) {
			return 'Maaltijd aanmaken';
		}
		return 'Maaltijd wijzigen';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		if ($this->_mid === 0) {
			$smarty->assign('nieuw', true);
		}
		$smarty->display('taken/popup_form.tpl');
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
		return $this->_form->valid(null);
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>
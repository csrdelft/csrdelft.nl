<?php
namespace Taken\MLT;

require_once 'formulier.class.php';

/**
 * RepetitieMaaltijdenFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor nieuwe periodieke maaltijden.
 * 
 */
class RepetitieMaaltijdenFormView extends \SimpleHtml {

	private $_form;
	
	public function __construct(MaaltijdRepetitie $repetitie, $beginDatum=null, $eindDatum=null) {
		
		$formFields[] = new \HTMLComment('<p>Aanmaken op de eerste '. $repetitie->getDagVanDeWeekText() .' en vervolgens '. $repetitie->getPeriodeInDagenText() .' in de periode:</p>');
		$formFields['begin'] = new \DatumField('begindatum', $beginDatum, 'Vanaf', date('Y')+1, date('Y'));
		$formFields['eind'] = new \DatumField('einddatum', $eindDatum, 'Tot en met', date('Y')+1, date('Y'));
		
		$this->_form = new \Formulier('taken-repetitie-aanmaken-form', $GLOBALS['taken_module'] .'/aanmaken/'. $repetitie->getMaaltijdRepetitieId(), $formFields);
	}
	
	public function getTitel() {
		return 'Periodieke maaltijden aanmaken';
	}
	
	public function view() {
		$smarty = new \TemplateEngine();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		$smarty->assign('nocheck', true);
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		$fields = $this->_form->getFields();
		if (strtotime($fields['eind']->getValue()) < strtotime($fields['begin']->getValue())) {
			$fields['eind']->error = 'Moet na begindatum liggen';
			return false;
		}
		return $this->_form->valid();
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>
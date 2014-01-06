<?php
namespace Taken\MLT;

require_once 'formulier.class.php';

/**
 * BoekjaarSluitenFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor het sluiten van het MaalCie-boekjaar.
 * 
 */
class BoekjaarSluitenFormView extends \SimpleHtml {

	private $_form;
	
	public function __construct($beginDatum=null, $eindDatum=null) {
		
		$formFields[] = new \HTMLComment('<p style="color:red;">Dit is een onomkeerbare stap!</p>');
		$formFields['begin'] = new \DatumField('begindatum', $beginDatum, 'Vanaf', date('Y')+1, date('Y')-2);
		$formFields['eind'] = new \DatumField('einddatum', $eindDatum, 'Tot en met', date('Y')+1, date('Y')-2);
		
		$this->_form = new \Formulier('taken-boekjaar-sluiten-form', $GLOBALS['taken_module'] .'/sluitboekjaar', $formFields);
	}
	
	public function getTitel() {
		return 'Boekjaar sluiten';
	}
	
	public function view() {
		$smarty = new \Smarty3CSR();
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
		return $this->_form->valid(null);
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>
<?php
namespace Taken\CRV;

require_once 'formulier.class.php';

/**
 * VrijstellingFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken vrijstelling.
 * 
 */
class VrijstellingFormView extends \SimpleHtml {

	private $_form;
	private $_uid;
	
	public function __construct($uid=null, $begin=null, $eind=null, $percentage=null) {
		$this->_uid = $uid;
		
		$formFields[] = new \RequiredLidField('lid_id', $uid, 'Naam of lidnummer');
		$formFields[] = new \DatumField('begin_datum', $begin, 'Vanaf', date('Y')+1, date('Y'));
		$formFields[] = new \DatumField('eind_datum', $eind, 'Tot en met', date('Y')+1, date('Y'));
		$formFields[] = new \IntField('percentage', $percentage, 'Percentage (%)', $GLOBALS['vrijstelling_percentage_max'], $GLOBALS['vrijstelling_percentage_min']);
		
		$this->_form = new \Formulier('taken-vrijstelling-form', $GLOBALS['taken_module'] .'/opslaan'. ($uid === null ? '' : '/'. $uid), $formFields);
	}
	
	public function getTitel() {
		if ($this->_uid === null) {
			return 'Vrijstelling aanmaken'; 
		}
		return 'Vrijstelling wijzigen'; 
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		if ($this->_uid === null) {
			$smarty->assign('nocheck', true);
		}
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		return $this->_form->valid(null);
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>
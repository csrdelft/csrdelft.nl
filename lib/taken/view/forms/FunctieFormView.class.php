<?php
namespace Taken\CRV;

require_once 'formulier.class.php';

/**
 * FunctieFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken corveefunctie.
 * 
 */
class FunctieFormView extends \SimpleHtml {

	private $_form;
	private $_fid;
	
	public function __construct($fid, $naam=null, $afk=null, $email=null, $punten=null, $kwali=null) {
		$this->_fid = $fid;
		
		$formFields['naam'] = new \RequiredInputField('naam', $naam, 'Naam', 25);
		$formFields['naam']->forcenotnull = true;
		$formFields['afk'] = new \RequiredInputField('afkorting', $afk, 'Afkorting', 3);
		$formFields['afk']->forcenotnull = true;
		$formFields[] = new \TextField('email_bericht', $email, 'Email', 9);
		$formFields[] = new \IntField('standaard_punten', $punten, 'Standaard punten', 10, 0);
		$formFields['kwali'] = new \VinkField('kwalificatie_benodigd', $kwali, 'Kwalificatie benodigd');
		if ($this->_fid !== 0) {
			$formFields['kwali']->setOnChangeScript("if (!this.checked) alert('Alle kwalificaties zullen worden verwijderd!');");
		}
		
		$this->_form = new \Formulier('taken-functie-form', $GLOBALS['taken_module'] .'/opslaan/'. $fid, $formFields);
	}
	
	public function getTitel() {
		if ($this->_fid === 0) {
			return 'Corveefunctie aanmaken'; 
		}
		return 'Corveefunctie wijzigen'; 
	}
	
	public function view() {
		$smarty = new \Smarty3CSR();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		if ($this->_fid === 0) {
			$smarty->assign('nocheck', true);
		}
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		if (!is_int($this->_fid) || $this->_fid < 0) {
			return false;
		}
		return $this->_form->valid(null);
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>
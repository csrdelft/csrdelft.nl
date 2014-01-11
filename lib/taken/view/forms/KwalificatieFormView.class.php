<?php
namespace Taken\CRV;

require_once 'formulier.class.php';

/**
 * KwalificatieFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor het toewijzen van een corvee-kwalificatie.
 * 
 */
class KwalificatieFormView extends \SimpleHtml {

	private $_form;
	private $_fid;
	
	public function __construct($fid, $uid=null) {
		$this->_fid = $fid;
		
		$formFields[] = new \LidField('voor_lid', $uid, 'Naam of lidnummer', 'leden');
		
		$this->_form = new \Formulier('taken-kwalificatie-form', $GLOBALS['taken_module'] .'/kwalificeer/'. $fid, $formFields);
	}
	
	public function getTitel() {
		return 'Kwalificatie toewijzen';
	}
	
	public function view() {
		$smarty = new \TemplateEngine();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		if (!is_int($this->_fid) || $this->_fid <= 0) {
			return false;
		}
		return $this->_form->valid();
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>
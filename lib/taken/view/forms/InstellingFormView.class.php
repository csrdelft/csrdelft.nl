<?php
namespace Taken\MLT;

require_once 'formulier.class.php';

/**
 * InstellingFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken instelling.
 * 
 */
class InstellingFormView extends \SimpleHtml {

	private $_form;
	
	public function __construct($key=null, $value=null) {
		
		$formFields['key'] = new \RequiredInputField('instelling_id', $key, 'Id');
		$formFields['key']->forcenotnull = true;
		$formFields[] = new \AutoresizeTextField('waarde', $value, 'Waarde', 0);
		
		$this->_form = new \Formulier('taken-instelling-form', $GLOBALS['taken_module'] .'/opslaan/'. $key, $formFields);
	}
	
	public function getTitel() {
		return 'Instelling wijzigen'; 
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$this->_form->cssClass .= ' popup';
		$smarty->assign('form', $this->_form);
		$smarty->display('taken/popup_form.tpl');
	}
	
	public function validate() {
		$fields = $this->_form->getFields();
		$key = $fields['key']->getValue();
		if (!empty($key)) {
			if (preg_match('/\s/', $key)) {
				$fields['key']->error = 'Mag geen spaties bevatten';
				return false;
			}
		}
		return $this->_form->valid();
	}
	
	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}
}

?>
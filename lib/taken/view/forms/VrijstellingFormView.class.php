<?php

/**
 * VrijstellingFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken vrijstelling.
 * 
 */
class VrijstellingFormView extends TemplateView {

	private $_form;
	private $_uid;

	public function __construct($uid = null, $begin = null, $eind = null, $percentage = null) {
		parent::__construct();
		$this->_uid = $uid;

		$fields[] = new RequiredLidField('lid_id', $uid, 'Naam of lidnummer');
		$fields[] = new DatumField('begin_datum', $begin, 'Vanaf', date('Y') + 1, date('Y'));
		$fields[] = new DatumField('eind_datum', $eind, 'Tot en met', date('Y') + 1, date('Y'));
		$fields[] = new IntField('percentage', $percentage, 'Percentage (%)', Instellingen::get('corvee', 'vrijstelling_percentage_max'), Instellingen::get('corvee', 'vrijstelling_percentage_min'));
		$fields[] = new SubmitResetCancel();

		$this->_form = new Formulier('taken-vrijstelling-form', Instellingen::get('taken', 'url') . '/opslaan' . ($uid === null ? '' : '/' . $uid), $fields);
	}

	public function getTitel() {
		if ($this->_uid === null) {
			return 'Vrijstelling aanmaken';
		}
		return 'Vrijstelling wijzigen';
	}

	public function view() {
		$this->_form->css_classes[] = 'popup';
		$this->smarty->assign('form', $this->_form);
		if ($this->_uid === null) {
			$this->smarty->assign('nocheck', true);
		}
		$this->smarty->display('taken/popup_form.tpl');
	}

	public function validate() {
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>
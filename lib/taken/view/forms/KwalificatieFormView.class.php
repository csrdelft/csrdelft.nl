<?php

/**
 * KwalificatieFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor het toewijzen van een corvee-kwalificatie.
 * 
 */
class KwalificatieFormView extends TemplateView {

	private $_form;
	private $_fid;

	public function __construct($fid, $uid = null) {
		parent::__construct();
		$this->_fid = $fid;

		$fields[] = new LidField('voor_lid', $uid, 'Naam of lidnummer', 'leden');
		$fields[] = new SubmitResetCancel();

		$this->_form = new Formulier(null, 'taken-kwalificatie-form', Instellingen::get('taken', 'url') . '/kwalificeer/' . $fid, $fields);
	}

	public function getTitel() {
		return 'Kwalificatie toewijzen';
	}

	public function view() {
		$this->_form->css_classes[] = 'popup PreventUnchanged';
		$this->smarty->assign('form', $this->_form);
		$this->smarty->display('taken/popup_form.tpl');
	}

	public function validate() {
		if (!is_int($this->_fid) || $this->_fid <= 0) {
			return false;
		}
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>
<?php

/**
 * FunctieFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken corveefunctie.
 * 
 */
class FunctieFormView extends TemplateView {

	private $_form;
	private $_fid;

	public function __construct($fid, $naam = null, $afk = null, $email = null, $punten = null, $kwali = null) {
		parent::__construct();
		$this->_fid = $fid;

		$fields[] = new TextField('naam', $naam, 'Naam', 25);
		$fields[] = new TextField('afkorting', $afk, 'Afkorting', 3);
		$fields[] = new TextareaField('email_bericht', $email, 'Email', 9);
		$fields[] = new IntField('standaard_punten', $punten, 'Standaard punten', 10, 0);
		$fields['kwali'] = new VinkField('kwalificatie_benodigd', $kwali, 'Kwalificatie benodigd');
		if ($this->_fid !== 0) {
			$fields['kwali']->setOnChangeScript("if (!this.checked) alert('Alle kwalificaties zullen worden verwijderd!');");
		}
		$fields[] = new SubmitResetCancel();

		$this->_form = new Formulier(null, 'taken-functie-form', Instellingen::get('taken', 'url') . '/opslaan/' . $fid, $fields);
	}

	public function getTitel() {
		if ($this->_fid === 0) {
			return 'Corveefunctie aanmaken';
		}
		return 'Corveefunctie wijzigen';
	}

	public function view() {
		$this->_form->css_classes[] = 'popup PreventUnchanged';
		$this->smarty->assign('form', $this->_form);
		if ($this->_fid === 0) {
			$this->smarty->assign('nocheck', true);
		}
		$this->smarty->display('taken/popup_form.tpl');
	}

	public function validate() {
		if (!is_int($this->_fid) || $this->_fid < 0) {
			return false;
		}
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>
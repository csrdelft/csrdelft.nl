<?php

/**
 * RepetitieCorveeFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor nieuw periodiek corvee.
 * 
 */
class RepetitieCorveeFormView extends TemplateView {

	private $_form;

	public function __construct(CorveeRepetitie $repetitie, $beginDatum = null, $eindDatum = null, $mid = null) {
		parent::__construct();

		$fields[] = new HtmlComment('<p>Aanmaken op de eerste ' . $repetitie->getDagVanDeWeekText() . 'en vervolgens ' . $repetitie->getPeriodeInDagenText() . ' in de periode:</p>');
		$fields['begin'] = new DatumField('begindatum', $beginDatum, 'Vanaf', date('Y') + 1, date('Y'));
		$fields['eind'] = new DatumField('einddatum', $eindDatum, 'Tot en met', date('Y') + 1, date('Y'));
		$fields[] = new HiddenField('maaltijd_id', $mid);
		$fields[] = new SubmitResetCancel();

		$this->_form = new Formulier('taken-repetitie-aanmaken-form', Instellingen::get('taken', 'url') . '/aanmaken/' . $repetitie->getCorveeRepetitieId(), $fields);
	}

	public function getTitel() {
		return 'Periodiek corvee aanmaken';
	}

	public function view() {
		$this->_form->css_classes[] = 'popup';
		$this->smarty->assign('form', $this->_form);
		$this->smarty->assign('nocheck', true);
		$this->smarty->display('taken/popup_form.tpl');
	}

	public function validate() {
		$fields = $this->_form->getFields();
		if (strtotime($fields['eind']->getValue()) < strtotime($fields['begin']->getValue())) {
			$fields['eind']->error = 'Moet na begindatum liggen';
			return false;
		}
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>
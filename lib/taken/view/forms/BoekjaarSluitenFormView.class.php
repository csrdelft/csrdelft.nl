<?php



/**
 * BoekjaarSluitenFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor het sluiten van het MaalCie-boekjaar.
 * 
 */
class BoekjaarSluitenFormView extends TemplateView {

	private $_form;

	public function __construct($beginDatum = null, $eindDatum = null) {
		parent::__construct();
		$InputFields[] = new HtmlComment('<p style="color:red;">Dit is een onomkeerbare stap!</p>');
		$InputFields['begin'] = new DatumField('begindatum', $beginDatum, 'Vanaf', date('Y') + 1, date('Y') - 2);
		$InputFields['eind'] = new DatumField('einddatum', $eindDatum, 'Tot en met', date('Y') + 1, date('Y') - 2);

		$this->_form = new Formulier('taken-boekjaar-sluiten-form', $GLOBALS['taken_module'] . '/sluitboekjaar', $InputFields);
	}

	public function getTitel() {
		return 'Boekjaar sluiten';
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->_form->css_classes[] = 'popup';
		$this->assign('form', $this->_form);
		$this->assign('nocheck', true);
		$this->display('taken/popup_form.tpl');
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
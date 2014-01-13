<?php



/**
 * InstellingFormView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 *
 * Formulier voor een nieuwe of te bewerken instelling.
 * 
 */
class InstellingFormView extends TemplateView {

	private $_form;

	public function __construct($key = null, $value = null) {
		parent::__construct();
		$formFields['key'] = new TextField('instelling_id', $key, 'Id');
		$formFields[] = new AutoresizeTextareaField('waarde', $value, 'Waarde', 0);

		$this->_form = new Formulier('taken-instelling-form', $GLOBALS['taken_module'] . '/opslaan/' . $key, $formFields);
	}

	public function getTitel() {
		return 'Instelling wijzigen';
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->_form->css_classes[] = 'popup';
		$this->assign('form', $this->_form);
		$this->display('taken/popup_form.tpl');
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
		return $this->_form->validate();
	}

	public function getValues() {
		return $this->_form->getValues(); // escapes HTML
	}

}

?>
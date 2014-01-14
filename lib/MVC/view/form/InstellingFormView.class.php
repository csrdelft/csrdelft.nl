<?php

/**
 * InstellingFormView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Formulier voor een nieuwe of te bewerken instelling.
 * 
 */
class InstellingFormView extends TemplateView {

	private $form;

	public function __construct(Instelling $instelling) {
		parent::__construct($instelling);

		$formFields[] = new HiddenField('module', $instelling->module);
		$formFields['key'] = new TextField('instelling_id', $instelling->key, 'Id');
		$formFields[] = new AutoresizeTextareaField('waarde', $instelling->value, 'Waarde', 0);

		$this->form = new Formulier('taken-instelling-form', $GLOBALS['taken_module'] . '/opslaan/' . $instelling->module . '/' . $instelling->key, $formFields);
	}

	public function getTitel() {
		return 'Instelling wijzigen';
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->form->css_classes[] = 'popup';
		$this->assign('form', $this->form);
		$this->display('taken/popup_form.tpl');
	}

	public function validate() {
		$fields = $this->form->getFields();
		$key = $fields['key']->getValue();
		if (!empty($key)) {
			if (preg_match('/\s/', $key)) {
				$fields['key']->error = 'Mag geen spaties bevatten';
				return false;
			}
		}
		return $this->form->validate();
	}

	public function getValues() {
		return $this->form->getValues(); // escapes HTML
	}

}

?>
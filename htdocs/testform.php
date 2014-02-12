<?php

require_once 'configuratie.include.php';
require_once 'MVC/view/validator.interface.php';


/* start view */

class TestFormulierContent extends TemplateView implements Validator {

	protected $form;

	public function __construct($model) {
		parent::__construct($model);

		$fields[] = new Subkopje('Studie:');
		$fields[] = new StudieField('studie', $model->default, 'Studie');
		$fields[] = new RequiredTextareaField('opmerking1', $model->default, 'Opmerking1');
		$fields['pre'] = new UbbPreviewField('opmerking', $model->default, 'previewOnEnter:');
		$fields['pre']->previewOnEnter();
		$fields[] = new UidField('uidtest', '0436', 'Wie ben jij?');
		$fields[] = new VerticaleField('verticale', '4', 'Welke verticale?');
		$fields[] = new DatumField('datum', '2011-08-11', 'Welke datum?');
		$fields[] = new SubmitResetCancel('/communicatie/profiel/' . $model->uid, true, 'opslaan', 'annuleren', 'reset');
		$fields[] = new LidField('lidtest', 'x101', 'Wat is je naam?', 'alleleden');
		$fields[] = new LidField('lid2test', 'Gra', 'Wat is je naam?', 'nobodies');

		$this->form = new Formulier('test-form', '/testform.php', $fields);

		$properties = $this->form->getValues(); // fetch POST values
		$model->default = $properties['studie'];
		$model->uid = $properties['uidtest'];
	}

	public function getTitel() {
		return 'Testformulier';
	}

	public function view() {
		//gebruik smarty optioneel
		echo '<h1>Testformulier</h1>Wat autoaanvullen dingen testen, net als hippe ajax-inline-bewerkzaken...';
		$this->form->view();
		pr($this->getModel()); // TEST
	}

	public function validate() {
		return $this->form->validate();
	}

	public function getError() {
		return $this->form->getError();
	}

}

/* end view */


/* start model */
$model = (object) 'vies';
$model->default = 'test123';
$model->uid = '0436';

/* end model */


/* start controller */

$view = new TestFormulierContent($model);
if (isPosted()) { // fetches POST values itself
	if ($view->validate()) {
		setMelding('Save to DB here', 1);
	} else {
		setMelding($view->getError(), -1);
	}
}

$pagina = new csrdelft($view);
$pagina->zijkolom = false; //geen zijkolom, overzichterlijker debuggen
//$pagina->addStylesheet('profiel.css');
$pagina->addStylesheet('js/autocomplete/jquery.autocomplete.css');
//$pagina->addScript('profiel.js');
$pagina->addScript('autocomplete/jquery.autocomplete.min.js');
$pagina->view();

/* end controller */

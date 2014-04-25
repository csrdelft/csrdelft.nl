<?php

require_once 'configuratie.include.php';
require_once 'MVC/view/validator.interface.php';


/* start view */

class TestFormulier extends Formulier {

	public function __construct($model) {
		parent::__construct($model, 'test-form', '/testform.php');
		$this->titel = 'Testformulier';

		$fields[] = new Subkopje('Studie:');
		$fields[] = new StudieField('studie', $model->default, 'Studie');
		$fields[] = new RequiredTextareaField('opmerking1', $model->default, 'Opmerking1');
		$fields[] = new UbbPreviewField('opmerking', $model->default, 'previewOnEnter:', true);
		$fields[] = new UidField('uidtest', '0436', 'Wie ben jij?');
		$fields[] = new VerticaleField('verticale', '4', 'Welke verticale?');
		$fields[] = new DatumField('datum', '2011-08-11', 'Welke datum?');
		$fields[] = new SubmitResetCancel('/communicatie/profiel/' . $model->uid, true, 'opslaan', 'annuleren', 'reset');
		$fields[] = new LidField('lidtest', 'x101', 'Wat is je naam?', 'alleleden');
		$fields[] = new LidField('lid2test', 'Gra', 'Wat is je naam?', 'nobodies');

		$this->addFields($fields);
	}

	public function view() {
		echo getMelding();
		echo '<h1>Testformulier</h1><p>Wat autoaanvullen dingen testen, net als hippe ajax-inline-bewerkzaken...</p>';
		echo parent::view();
		debugprint($this->getModel());
	}

}

/* end view */


/* start model */
$model = (object) 'vies';
$model->studie = 'test123';
$model->uidtest = '0436';

/* end model */


/* start controller */

$view = new TestFormulier($model);
if (isPosted()) { // fetches POST values itself
	if ($view->validate()) {
		setMelding('Save to DB here', 1);
	} else {
		setMelding($view->getError(), -1);
	}
}

$pagina = new CsrLayoutPage($view);
$pagina->zijkolom = false; //geen zijkolom, overzichterlijker debuggen
//$pagina->addStylesheet('profiel.css');
$pagina->addStylesheet('js/autocomplete/jquery.autocomplete.css');
//$pagina->addScript('profiel.js');
$pagina->addScript('autocomplete/jquery.autocomplete.min.js');
$pagina->view();

/* end controller */

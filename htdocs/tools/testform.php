<?php

require_once 'configuratie.include.php';

/* start view */

class TestFormulier extends Formulier {

	public function __construct($model) {
		parent::__construct($model, 'test-form', '/testform.php');
		$this->titel = 'Testformulier';

		$fields[] = new HtmlComment('<p>Wat autoaanvullen dingen testen, net als hippe ajax-inline-bewerkzaken...</p>');
		$fields[] = new Subkopje('Studie:');
		$fields[] = new StudieField('studie', $model->studie, 'Studie');
		$fields[] = new RequiredTextareaField('opmerking1', null, 'Opmerking1');
		$fields['enter'] = new CsrBBPreviewField('opmerking', '', 'previewOnEnter:', true);
		$fields['enter']->previewOnEnter = true;
		$fields[] = new UidField('uidtest', '0436', 'Wie ben jij?');
		$fields[] = new VerticaleField('verticale', '4', 'Welke verticale?');
		$fields[] = new DatumField('datum', '2011-08-11', 'Welke datum?');
		$fields[] = new FormDefaultKnoppen('/profiel/' . $model->uidtest);
		$fields[] = new LidField('lidtest', 'x101', 'Wat is je naam?', 'alleleden');
		$fields[] = new LidField('lid2test', 'Gra', 'Wat is je naam?', 'nobodies');

		$this->addFields($fields);
	}

	public function view() {
		echo getMelding();
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

$form = new TestFormulier($model); // fetches POST values itself
if ($form->validate()) {
	setMelding('Save to DB here', 1);
} else {
	setMelding($form->getError(), -1);
}

$pagina = new CsrLayoutPage($form);
$pagina->view();

/* end controller */
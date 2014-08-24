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
		$fields['enter'] = new UbbPreviewField('opmerking', '', 'previewOnEnter:', true);
		$fields['enter']->previewOnEnter = true;
		$fields[] = new UidField('uidtest', '0436', 'Wie ben jij?');
		$fields[] = new VerticaleField('verticale', '4', 'Welke verticale?');
		$fields[] = new DatumField('datum', '2011-08-11', 'Welke datum?');
		$fields[] = new FormButtons('/communicatie/profiel/' . $model->uidtest);
		$fields[] = new LidField('lidtest', 'x101', 'Wat is je naam?', 'alleleden');
		$fields[] = new LidField('lid2test', 'Gra', 'Wat is je naam?', 'nobodies');

		$this->addFields($fields);
	}

	public function view() {
		echo SimpleHTML::getMelding();
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
	SimpleHTML::setMelding('Save to DB here', 1);
} else {
	SimpleHTML::setMelding($form->getError(), -1);
}

$pagina = new CsrLayoutPage($form);
$pagina->zijkolom = false; //geen zijkolom, overzichterlijker debuggen
//$pagina->addStylesheet('/layout/css/profiel');
//$pagina->addScript('/layout/js/profiel');
$pagina->view();

/* end controller */
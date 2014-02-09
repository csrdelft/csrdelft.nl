<?php

require_once 'configuratie.include.php';

$default = 'test123';
$uid = '0436';

//testformulier bouwen.
$fields[] = new Subkopje('Studie:');
$fields[] = new StudieField('studie', $default, 'Studie');
$fields[] = new TextareaField('opmerking1', $default, 'Opmerking1');
$fields['pre'] = new UbbPreviewField('opmerking', $default, 'previewOnEnter:');
$fields['pre']->previewOnEnter();
$fields[] = new UidField('uidtest', '0436', 'Wie ben jij?');
$fields[] = new VerticaleField('verticale', '4', 'Welke verticale?');
$fields[] = new DatumField('datum', '2011-08-11', 'Welke datum?');
$fields[] = new SubmitResetCancel('/communicatie/profiel/' . $uid, true, 'opslaan', 'annuleren', 'reset');
$fields[] = new LidField('lidtest', 'x101', 'Wat is je naam?', 'alleleden');
$fields[] = new LidField('lid2test', 'Gra', 'Wat is je naam?', 'nobodies');

$form = new Formulier('test-form', '/testform.php', $fields);

class TestFormulierContent extends TemplateView {

	public function __construct($form) {
		parent::__construct();
		$this->form = $form;
	}

	public function getTitel() {
		return 'Testformulier';
	}

	public function view() {
		echo '<h1>Testformulier</h1>Wat autoaanvullen dingen testen, net als hippe ajax-inline-bewerkzaken...';
		$this->form->view();
	}

}

if ($form->validate()) {
	pr($form);
}

$midden = new TestFormulierContent($form);
$pagina = new csrdelft($midden);

//geen zijkolom, overzichterlijker debuggen
$pagina->zijkolom = false;
$pagina->addStylesheet('profiel.css');
$pagina->addStylesheet('js/autocomplete/jquery.autocomplete.css');
//$pagina->addScript('profiel.js');
$pagina->addScript('autocomplete/jquery.autocomplete.min.js');


$pagina->view();




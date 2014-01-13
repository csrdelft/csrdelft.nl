<?php

require_once 'configuratie.include.php';



$default='test123';
$uid='0436';

$form=array();

//testformulier bouwen.
$form[]=new Subkopje('Studie:');
$form[]=new StudieField('studie', $default, 'Studie');
$form[]=new TextareaField('opmerking1', $default, 'Opmerking1');
$hippeTextarea=new UbbPreviewField('opmerking', $default, 'previewOnEnter:');
$hippeTextarea->previewOnEnter();

$form[]=$hippeTextarea;


$form[]=new UidField('uidtest', '0436', 'Wie ben jij?');
$form[]=new VerticaleField('verticale', '4', 'Welke verticale?');
$form[]=new DatumField('datum', '2011-08-11', 'Welke datum?');
$form[]=new SubmitButton('opslaan', '<a class="knop" href="/communicatie/profiel/'.$uid.'">Annuleren</a>');
$form[]=new LidField('lidtest', 'x101', 'Wat is je naam?', 'alleleden');
$form[]=new LidField('lid2test', 'Gra', 'Wat is je naam?', 'nobodies');


$form=new Formulier('test-form', '/testform.php', $form);

class TestFormulierContent extends TemplateView{
	public function __construct($form){
		parent::__construct();
		$this->form=$form;
	}
	public function getTitel(){ return 'Testformulier'; }
	
	public function view(){
		echo '<h1>Testformulier</h1>Wat autoaanvullen dingen testen, net als hippe ajax-inline-bewerkzaken...';
		$this->form->view();
	}
}


if($form->validate()){
	pr($form);
}

$midden=new TestFormulierContent($form);
$pagina=new csrdelft($midden);

//geen zijkolom, overzichterlijker debuggen
$pagina->zijkolom=false;
$pagina->addStylesheet('profiel.css');
$pagina->addStylesheet('js/autocomplete/jquery.autocomplete.css');
//$pagina->addScript('profiel.js');
$pagina->addScript('autocomplete/jquery.autocomplete.min.js');


$pagina->view();




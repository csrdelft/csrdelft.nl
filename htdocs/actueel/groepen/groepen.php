<?php
/*
 * groepen.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Overzicht van de h.t. groepen per groepcategorie
 */

require_once 'configuratie.include.php';

require_once 'groepen/groep.class.php';
require_once 'groepen/groepcontent.class.php';
require_once 'groepen/groepcontroller.class.php';


if(isset($_GET['gtype'])){
	$gtype=$_GET['gtype'];
}else{
	$gtype="Commissies";
}

try{
	$groepen=new Groepen($gtype);

	$content=new Groepencontent($groepen);
}catch(Exception $e){
	GroepenContent::invokeRefresh('Groeptype ('.mb_htmlentities($gtype).') bestaat niet', '/actueel/groepen/');
}

if(isset($_GET['maakOt']) AND $groepen->isAdmin()){
	if($groepen->maakGroepenOt()){
		GroepenContent::invokeRefresh('De h.t. groepen in deze categorie zijn met succes o.t. gemaakt.', '/actueel/groepen/'.$groepen->getNaam());
	}else{
		GroepenContent::invokeRefresh('De h.t. groepen zijn niet allemaal met succes o.t. gemaakt.', '/actueel/groepen/'.$groepen->getNaam());
	}
}
if(isset($_GET['bewerken']) AND $groepen->isAdmin()){
	$content->setAction('edit');
	if($_SERVER['REQUEST_METHOD']=='POST'){
		if(isset($_POST['beschrijving'])){
			$groepen->setBeschrijving($_POST['beschrijving']);
			if($groepen->save()){
				GroepenContent::invokeRefresh('Beschrijving van groepstype met succes opgeslagen.', '/actueel/groepen/'.$groepen->getNaam());
			}else{
				$content->setMelding('Opslaan mislukt');
			}
		}else{
			$content->setMelding('Opslaan mislukt. Geen inhoud gevonden. ');
		}
	}
}


$pagina=new csrdelft($content);
$pagina->addStylesheet('groepen.css');
$pagina->addScript('groepen.js');
$pagina->view();
?>

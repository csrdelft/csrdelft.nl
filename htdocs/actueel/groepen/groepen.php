<?php
/*
 * groepen.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Overzicht van de h.t. groepen per groepcategorie
 */

require_once 'include.config.php';

require_once 'groepen/class.groepen.php';
require_once 'groepen/class.groep.php';
require_once 'groepen/class.groepcontent.php';

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

if(isset($_GET['maakOt']) AND LoginLid::instance()->hasPermission('P_ADMIN')){
	if($groepen->maakGroepenOt()){
		GroepenContent::invokeRefresh('De h.t. groepen in deze categorie zijn met succes o.t. gemaakt.', '/actueel/groepen/'.$groepen->getNaam());
	}else{
		GroepenContent::invokeRefresh('De h.t. groepen zijn niet allemaal met succes o.t. gemaakt.', '/actueel/groepen/'.$groepen->getNaam());
	}
}
	
	

$pagina=new csrdelft($content);
$pagina->addStylesheet('groepen.css');
$pagina->addScript('groepen.js');
$pagina->view();
?>

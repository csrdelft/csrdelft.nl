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

$content=new Groepencontent(new Groepen($gtype));


$pagina=new csrdelft($content);
$pagina->addStylesheet('groepen.css');
$pagina->addScript('groepen.js');
$pagina->view();
?>

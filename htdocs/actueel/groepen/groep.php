<?php
/*
 * groep.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 * 
 * 
 */
 

require_once('include.config.php');

require_once('class.groep.php');
require_once('class.groepcontent.php');
require_once('class.groepcontroller.php');

if(!isset($_GET['query'])){
	echo 'querystring niet aanwezig, dat gaat hiet werken (htdocs/groep.php)';
	exit;
}	
$controller=new Groepcontroller($_GET['query']);

## zijkolom in elkaar jetzen
	$zijkolom=new kolom();

# pagina weergeven
$pagina=new csrdelft($controller->getContent());
$pagina->setZijkolom($zijkolom);
$pagina->addStylesheet('groepen.css');
$pagina->view();
?>
?>

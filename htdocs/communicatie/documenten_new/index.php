<?php
/*
 * index.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Documentenketzerding.
 */
require_once 'include.config.php';

require_once 'documenten/class.documentcontroller.php';

if(isset($_GET['querystring'])){
	$docControl=new DocumentController($_GET['querystring']);
}else{
	die('epic fail');
}

$pagina=new csrdelft($docControl->getContent());
$pagina->addStylesheet('documenten.css');

$pagina->addScript('jquery.js');
$pagina->addScript('documenten.js');
$pagina->view();
?>

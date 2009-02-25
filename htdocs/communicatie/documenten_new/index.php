<?php
/*
 * index.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 *
 */
require_once 'include.config.php';

require_once 'documenten/class.documentcontroller.php';

if(isset($_GET['querystring'])){
	$docControl=new DocumentController($_GET['querystring']);
}

$pagina=new csrdelft($docControl->getContent());
$pagina->addStylesheet('documenten.css');
$pagina->view();
?>

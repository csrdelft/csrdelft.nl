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

$zijkolom=new kolom();


$pagina=new csrdelft($docControl->getContent());
$pagina->setZijkolom($zijkolom);
$pagina->addStylesheet('documenten.css');
$pagina->view();
?>

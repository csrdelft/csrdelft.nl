<?php

/**
 * index.php	| 	Gerrit Uitslag (klapinklapin@gmail.com)
 *
 * Bibliotheek
 */
require_once 'configuratie.include.php';
require_once 'bibliotheek/bibliotheekcontroller.class.php';

if (isset($_GET['querystring'])) {
	$biebControl = new BibliotheekController($_GET['querystring']);
	$biebControl->performAction();
} else {
	die('epic fail');
}

$pagina = new CsrLayoutPage($biebControl->getContent());
if ($biebControl->getContent() instanceof BibliotheekCatalogusContent) {
	$pagina->zijkolom = false;
}

$pagina->addStylesheet('bibliotheek.css');
$pagina->addStylesheet('js/datatables/css/datatables_basic.css');
$pagina->addScript('datatables/jquery.dataTables.min.js');
$pagina->addScript('csrdelft.js');
$pagina->addScript('bibliotheek.js');
$pagina->view();

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

$pagina = new CsrLayoutPage($biebControl->getView());
if ($biebControl->getView() instanceof BibliotheekCatalogusContent) {
	$pagina->zijkolom = false;
}

$pagina->addStylesheet('/layout/css/bibliotheek.css');
$pagina->addStylesheet('/layout/js/datatables/css/datatables_basic.css');
$pagina->addScript('/layout/js/datatables/jquery.dataTables.min.js');
$pagina->addScript('/layout/js/csrdelft.js');
$pagina->addScript('/layout/js/bibliotheek.js');
$pagina->view();

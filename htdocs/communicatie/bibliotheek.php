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
//if ($biebControl->getView() instanceof BibliotheekCatalogusContent) {
//	$pagina->zijbalk = false;
//}
$pagina->addCompressedResources('datatable');
$pagina->addCompressedResources('bibliotheek');
$pagina->view();

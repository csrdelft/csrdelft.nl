<?php

require_once 'configuratie.include.php';
require_once 'documenten/documentcontroller.class.php';

/**
 * index.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 * Documentenketzerding.
 */
if (isset($_GET['querystring'])) {
	$docControl = new DocumentController($_GET['querystring']);
	$docControl->performAction();
} else {
	die('epic fail');
}

$pagina = new CsrLayoutPage($docControl->getView());
$pagina->addStylesheet('/layout/js/datatables/css/datatables_basic.css');
$pagina->addStylesheet('/layout/css/documenten.css');

$pagina->addScript('/layout/js/datatables/jquery.dataTables.min.js');
$pagina->addScript('/layout/js/documenten.js');

$pagina->view();

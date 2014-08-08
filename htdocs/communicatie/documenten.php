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

$pagina = new CsrLayoutPage($docControl->getContent());
$pagina->addStylesheet('datatables_basic.css', 'datatables_basic.css', '/layout/js/datatables/css/');
$pagina->addStylesheet('documenten.css');

$pagina->addScript('datatables/jquery.dataTables.min.js');
$pagina->addScript('documenten.js');

$pagina->view();

<?php

/**
 * groep.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 */
require_once 'configuratie.include.php';

require_once 'groepen/groep.class.php';
require_once 'groepen/groepcontent.class.php';
require_once 'groepen/groepcontroller.class.php';

if (!isset($_GET['query'])) {
	echo 'querystring niet aanwezig, dat gaat hiet werken (htdocs/groep.php)';
	exit;
}
$controller = new Groepcontroller($_GET['query']);
$controller->performAction();

$pagina = new CsrLayoutPage($controller->getView());

$pagina->addStylesheet('/layout/css/groepen');
$pagina->addScript('/layout/js/groepen');
$pagina->view();

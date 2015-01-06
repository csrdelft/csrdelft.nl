<?php

/**
 * groep.php	| 	Jan Pieter Waagmeester (jieter@jpwaag.com)
 *
 */
require_once 'configuratie.include.php';

if (!LoginModel::mag('P_LOGGED_IN')) { // nieuwe layout altijd voor uitgelogde bezoekers
	redirect('/vereniging');
}

require_once 'model/entity/groepen/OldGroep.class.php';
require_once 'view/groepen/OldGroepenView.class.php';
require_once 'controller/GroepenController.class.php';

if (!isset($_GET['query'])) {
	echo 'querystring niet aanwezig, dat gaat hiet werken (htdocs/groep.php)';
	exit;
}
$controller = new GroepController($_GET['query']);
$controller->performAction();

$pagina = new CsrLayoutPage($controller->getView());
$pagina->addCompressedResources('groepen');
$pagina->view();

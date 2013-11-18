<?php
/**
 * index.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Entry point van het Taken-systeem.
 * 
 */
try {
	require_once 'configuratie.include.php';
	require_once 'taken/model/InstellingenModel.class.php';
	require_once 'taken/controller/ModuleController.class.php';
	
	\Taken\MLT\InstellingenModel::loadAlleInstellingen();
	$controller = new \Taken\CRV\ModuleController($_GET['query']);
	$controller->getContent()->view();
}
catch (\Exception $e) { //TODO log all exceptions
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 '. $e->getMessage(), true, 500);
	echo $e; //DEBUG
}

?>
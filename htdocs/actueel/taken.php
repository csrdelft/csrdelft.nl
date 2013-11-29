<?php
/**
 * taken.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Entry point van het Taken-systeem.
 * 
 */
try {
	require_once 'configuratie.include.php';
	require_once 'taken/model/InstellingenModel.class.php';
	require_once 'taken/controller/ModuleController.class.php';
	
	\Taken\MLT\InstellingenModel::getAlleInstellingen();
	
	$query = $_GET['query'];
	if (substr($query, 0, 1) === '/') { // redir /maaltijden/ketzer equal to /maaltijdenketzer
		$query = substr($query, 1);
	}
	$controller = new \Taken\CRV\ModuleController($_GET['module'], $query); // module redir in .htaccess
	
	$GLOBALS['taken_mainmenu'] = '/actueel/';
	$GLOBALS['taken_module'] = $GLOBALS['taken_mainmenu'] . $controller->module;
	
	$controller->performAction($query);
	$controller->getContent()->view();
}
catch (\Exception $e) { //TODO log all exceptions

	header($_SERVER['SERVER_PROTOCOL'] . ' 500 '. $e->getMessage(), true, 500);
	
	if (defined('DEBUG') && (\LoginLid::instance()->hasPermission('P_ADMIN') || \LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}

?>
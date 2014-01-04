<?php
/**
 * taken.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Entry point voor Taken module.
 */
try {
	require_once 'configuratie.include.php';
	require_once 'taken/controller/ModuleController.class.php';
	
	$_SERVER['REQUEST_URI'] = $GLOBALS['taken_menu_path']; // fake path for main menu
	
	$query = filter_input(INPUT_GET, 'uri', FILTER_SANITIZE_URL);
	$controller = new \Taken\CRV\ModuleController($query);
	$controller->getContent()->view();
}
catch (\Exception $e) { // TODO: logging
	
	$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
	header($protocol . ' 500 '. $e->getMessage(), true, 500);
	
	if (defined('DEBUG') && (\LoginLid::instance()->hasPermission('P_ADMIN') || \LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}

?>
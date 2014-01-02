<?php
/**
 * taken.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Entry point van het Taken-systeem.
 * 
 */
try {
	require_once 'configuratie.include.php';
	require_once 'taken/controller/ModuleController.class.php';
	
	$query = filter_input(INPUT_GET, 'taken', FILTER_SANITIZE_URL);
	$_SERVER['REQUEST_URI'] = $GLOBALS['taken_menu_path'] . $query; // fake path for main menu
	
	$controller = new \Taken\CRV\ModuleController($query);
	$controller->getContent()->view();
}
catch (\Exception $e) {
	$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
	header($protocol . ' 500 '. $e->getMessage(), true, 500);
	
	if (defined('DEBUG') && (\LoginLid::instance()->hasPermission('P_ADMIN') || \LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}

?>
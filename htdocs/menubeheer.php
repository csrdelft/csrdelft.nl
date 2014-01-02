<?php
/**
 * menubeheer.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Entry point van het menu-beheer.
 * 
 */
try {
	require_once 'configuratie.include.php';
	require_once 'menu/beheer/BeheerMenuController.class.php';
	
	$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_URL);
	$controller = new BeheerMenuController($query); // module redir in .htaccess
	$controller->getContent()->view();
}
catch (\Exception $e) {
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 '. $e->getMessage(), true, 500);
	
	if (defined('DEBUG') && (\LoginLid::instance()->hasPermission('P_ADMIN') || \LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}

?>
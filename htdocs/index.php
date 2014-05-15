<?php

/**
 * index.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Entry point voor stek modules.
 */
try {
	require_once 'configuratie.include.php';

	$class = filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING);

	if (empty($class)) {
		$class = 'CmsPagina';
	}
	$class .= 'Controller';

	require_once 'MVC/controller/' . $class . '.class.php';
	$controller = new $class(Instellingen::get('stek', 'request'));

	if (defined('DB_MODIFY_ENABLE') AND LoginLid::mag('P_ADMIN')) {
		header('Content-Type: text/plain');
		header('Content-disposition: attachment;filename=DB_modify_' . time() . '.sql');
		foreach (DatabaseAdmin::getQueries() as $query) {
			echo $query . "\n";
		}
		exit;
	}

	$controller->getContent()->view();
}
catch (Exception $e) { // TODO: logging
	$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
	header($protocol . ' 500 ' . $e->getMessage(), true, 500);

	if (defined('DEBUG') && (LoginLid::mag('P_ADMIN') || LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}

// als er een error is geweest, die unsetten...
if (isset($_SESSION['auth_error'])) {
	unset($_SESSION['auth_error']);
}
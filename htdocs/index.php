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

	$req = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
	$class = filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING);

	if (empty($class)) {
		$class = 'CmsPagina';
	}
	$class .= 'Controller';

	require_once 'MVC/controller/' . $class . '.class.php';
	$controller = new $class($req);
	$controller->getContent()->view();
}
catch (Exception $e) { // TODO: logging

	$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
	header($protocol . ' 500 ' . $e->getMessage(), true, 500);

	if (defined('DEBUG') && (LoginLid::instance()->hasPermission('P_ADMIN') || LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}

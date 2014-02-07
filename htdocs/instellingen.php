<?php

/**
 * lidinstellingen.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Entry point voor instellingen van leden.
 * 
 */
try {
	require_once 'configuratie.include.php';
	require_once 'MVC/controller/LidInstellingenController.class.php';

	$query = filter_input(INPUT_GET, 'uri', FILTER_SANITIZE_URL);
	$controller = new LidInstellingenController($query);
	$controller->getContent()->view();
}
catch (\Exception $e) { // TODO: logging
	$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
	header($protocol . ' 500 ' . $e->getMessage(), true, 500);

	if (defined('DEBUG') && (\LoginLid::instance()->hasPermission('P_ADMIN') || \LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}

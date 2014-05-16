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

	$request = Instellingen::get('stek', 'request');

	require_once 'MVC/controller/' . $class . '.class.php';
	$controller = new $class($request);

	if (defined('DB_MODIFY_ENABLE') AND LoginLid::mag('P_ADMIN')) {
		$queries = DatabaseAdmin::getQueries();
		if (empty($queries)) {
			debugprint('DB_MODIFY_ENABLED');
		} else {
			header('Content-Type: text/plain');
			header('Content-disposition: attachment;filename=DB_modify_' . time() . '.sql');
			foreach ($queries as $query) {
				echo $query . ";\n";
				setMelding($query, 1);
			}
			exit;
		}
	}

	$controller->getContent()->view();
}
catch (Exception $e) {
	$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	$code = ($e->getCode() >= 100 ? $e->getCode() : 500);
	header($protocol . ' ' . $code . ' ' . $e->getMessage());
	DebugLogModel::instance()->log($class, '__construct', array($request), $e);

	if (defined('DEBUG') && (LoginLid::mag('P_ADMIN') || LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace 
	}
}

// als er een error is geweest, die unsetten...
if (isset($_SESSION['auth_error'])) {
	unset($_SESSION['auth_error']);
}
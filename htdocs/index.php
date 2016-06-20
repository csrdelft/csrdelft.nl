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

	// start MVC
	$class = filter_input(INPUT_GET, 'c', FILTER_SANITIZE_STRING);

	if (empty($class)) {
		$class = 'CmsPagina';
	}
	// toegang tot leden website dicht-timmeren:
	switch ($class) {
		// toegestaan voor iedereen:
		case 'Login':
		case 'CmsPagina':
		case 'Forum':
		case 'FotoAlbum':
		case 'Agenda':
		case 'Mededelingen':
			break;

		// de rest alleen voor ingelogde gebruikers:
		default:
			if (!LoginModel::mag('P_LOGGED_IN')) {
				redirect(CSR_ROOT);
			}
	}
	$class .= 'Controller';

	require_once 'controller/' . $class . '.class.php';
	$controller = new $class(REQUEST_URI);
	$controller->performAction();

	if (DB_CHECK AND LoginModel::mag('P_ADMIN')) {

		$queries = DatabaseAdmin::getQueries();
		if (!empty($queries)) {
			if (DB_MODIFY) {
				header('Content-Type: text/x-sql');
				header('Content-Disposition: attachment;filename=DB_modify_' . time() . '.sql');
				foreach ($queries as $query) {
					echo $query . ";\n";
				}
				exit;
			} else {
				debugprint($queries);
			}
		}
	}

	if (TIME_MEASURE) {
		TimerModel::instance()->time();
	}

	$controller->getView()->view();
	// einde MVC
} catch (Exception $e) {
	http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);
	fatal_handler($e);
}
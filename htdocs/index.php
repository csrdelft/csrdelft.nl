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
	// Toegang tot leden website dicht-timmeren
	switch ($class) {
		case 'Login':
		case 'CmsPagina':
		case 'Forum':
		case 'FotoAlbum':
		case 'Agenda':
			break; // toegestaan voor iedereen
		default: // alleen ingelogde gebruikers
			if (!LoginModel::mag('P_LOGGED_IN')) {
				redirect(CSR_ROOT);
			}
	}
	$class .= 'Controller';

	require_once 'MVC/controller/' . $class . '.class.php';
	$controller = new $class(REQUEST_URI);
	$controller->performAction();

	if (DB_CHECK AND LoginModel::mag('P_ADMIN')) {

		require_once 'MVC/model/framework/DatabaseAdmin.singleton.php';
		$queries = DatabaseAdmin::getQueries();

		if (empty($queries)) {
			if (DB_MODIFY AND ! DEBUG) {
				setMelding('DB_MODIFY ENABLED', 2);
			}
		} elseif (DB_MODIFY) {
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

	if (TIME_MEASURE) {
		TimerModel::instance()->time();
	}

	$controller->getView()->view();
}
catch (Exception $e) {
	$code = ($e->getCode() >= 100 ? $e->getCode() : 500);

	if (LoginModel::mag('P_ADMIN') OR LoginModel::instance()->isSued()) {
		http_status($code, $e->getMessage());

		echo str_replace('#', '<br />#', $e); // stacktrace 
		echo '<br />DatabaseAdmin queries:<br /><pre>';
		require_once 'MVC/model/framework/DatabaseAdmin.singleton.php';
		debugprint(DatabaseAdmin::getQueries());
		echo '</pre>';
	} else {
		http_status($code);
	}

	DebugLogModel::instance()->log('index.php', 'new ' . $class, array(REQUEST_URI), $e);
}

<?php

require_once 'configuratie.include.php';

if (!LoginLid::mag('P_ADMIN')) {
	header('location: ' . CSR_ROOT);
	exit;
}
$tables = array();
foreach (DatabaseAdmin::instance()->sqlShowTables()->fetchAll() as $table) {
	$tables[$table[0]] = $table[0];
}
$fields['actie'] = new KeuzeRondjeField('actie', null, 'Actie', array('S' => 'Structure', 'D' => 'Data'));
$fields['tabel'] = new SelectField('tabel', null, 'Tabel', $tables);
$fields['file'] = new FileField();
$fields['btn'] = new SubmitResetCancel(CSR_ROOT, true, true, false);
$form = new Formulier(null, 'form', null, $fields);
$form->titel = 'Dump database table';
try {
	switch ($fields['actie']->getValue()) {
		case 'D':
			if ($fields['tabel']->validate()) {
				DatabaseAdmin::instance()->sqlBackupTable($fields['tabel']->getValue());
			}
			exit;
		case 'S':
			if ($fields['tabel']->validate()) {
				debugprint(DatabaseAdmin::instance()->sqlShowCreateTable($fields['tabel']->getValue()));
			}
		default:
			$view = new CsrLayoutPage($form);
			$view->view();
	}
} catch (Exception $e) { // TODO: logging
	if (isset($path)) {
		unlink($path);
	}
	$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
	header($protocol . ' 500 ' . $e->getMessage(), true, 500);

	if (defined('DEBUG') && (LoginLid::mag('P_ADMIN') || LoginLid::instance()->isSued())) {
		echo str_replace('#', '<br />#', $e); // stacktrace
	}
}
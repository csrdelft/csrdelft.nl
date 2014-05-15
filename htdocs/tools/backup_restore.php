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
$fields['actie'] = new KeuzeRondjeField('actie', null, 'Actie', array('B' => 'Backup', 'R' => 'Restore'));
$fields['tabel'] = new SelectField('tabel', null, 'Tabel', $tables);
$fields['file'] = new FileField();
$fields['btn'] = new SubmitResetCancel(CSR_ROOT, true, true, false);
$form = new Formulier(null, 'form', null, $fields);
$form->titel = 'Backup/Restore database table';

if ($fields['actie']->getValue() === 'R' AND $form->validate()) {
	$path = TMP_PATH . '/';
	$tablename = $fields['tabel']->getValue() . '_' . time();
	$fields['file']->opslaan($path, $filename);
	$path .= $filename;
	try {
		$rows = DatabaseAdmin::instance()->sqlRestoreTable($fields['tabel']->getValue(), $path);
		echo $rows . ' rows restored';
		unlink($path);
	} catch (Exception $e) { // TODO: logging
		unlink($path);
		$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
		header($protocol . ' 500 ' . $e->getMessage(), true, 500);

		if (defined('DEBUG') && (LoginLid::mag('P_ADMIN') || LoginLid::instance()->isSued())) {
			echo str_replace('#', '<br />#', $e); // stacktrace
		}
	}
} elseif ($fields['actie']->getValue() === 'B' AND $fields['tabel']->validate()) {
	$table = $fields['tabel']->getValue();
	// Backup table
	$path = DatabaseAdmin::instance()->sqlBackupTable($table);
	header('Content-Type: text/plain');
	header('Content-disposition: attachment;filename=' . basename($path) . '.txt');
	echo DatabaseAdmin::instance()->sqlShowCreateTable($table) . "\n\n"; // table structure
	readfile($path); // table data
	unlink($path);
} else {
	$view = new CsrLayoutPage($form);
	$view->view();
}
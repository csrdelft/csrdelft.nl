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
$fields['actie'] = new KeuzeRondjeField('actie', null, 'Actie', array('S' => 'Backup structure', 'B' => 'Backup data', 'R' => 'Restore data'));
$fields['tabel'] = new SelectField('tabel', null, 'Tabel', $tables);
$fields['tabel']->onchange = 'document.getElementById("sql").value=this.value;';
$fields['file'] = new FileField();
$fields['btn'] = new SubmitResetCancel(CSR_ROOT, true, true, false);
$fields['html'] = new HtmlComment('<div id="sql"></div>');
$form = new Formulier(null, 'form', null, $fields);
$form->titel = 'Backup/Restore database table';

switch ($fields['actie']->getValue()) {
	case 'R' :
		if ($form->validate()) {
			$path = TMP_PATH . '/';
			$filename = $fields['tabel']->getValue() . '_' . time();
			$fields['file']->opslaan($path, $filename);
			$path .= $filename;
			try {
				$rows = DatabaseAdmin::instance()->sqlRestoreTable($fields['tabel']->getValue(), $path);
				unlink($path);
				invokeRefresh(null, $rows . ' rows restored', 1);
			} catch (Exception $e) { // TODO: logging
				unlink($path);
				$protocol = filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING);
				header($protocol . ' 500 ' . $e->getMessage(), true, 500);

				if (defined('DEBUG') && (LoginLid::mag('P_ADMIN') || LoginLid::instance()->isSued())) {
					echo str_replace('#', '<br />#', $e); // stacktrace
				}
			}
		}
		exit;
	case 'B':
		if ($fields['tabel']->validate()) {
// Backup table
			$path = DatabaseAdmin::instance()->sqlBackupTable($fields['tabel']->getValue());
			header('Content-Type: text/plain');
			header('Content-disposition: attachment;filename=' . basename($path) . '.txt');
			readfile($path); // table data
			unlink($path);
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
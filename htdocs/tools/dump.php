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

$fields['tabel'] = new SelectField('tabel', null, 'Tabel', $tables);
$fields['btn'] = new SubmitResetCancel(CSR_ROOT, true, true, false);
$form = new Formulier(null, 'form', null);
$form->addFields($fields);
$form->titel = 'Dump database table';

if ($form->validate()) {
	DatabaseAdmin::instance()->sqlBackupTable($fields['tabel']->getValue());
} else {
	$view = new CsrLayoutPage($form);
	$view->view();
}
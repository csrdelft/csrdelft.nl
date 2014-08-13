<?php

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_ADMIN')) {
	invokeRefresh(CSR_ROOT);
}

require_once 'MVC/model/DatabaseAdmin.singleton.php';

$tables = array();
$results = DatabaseAdmin::instance()->sqlShowTables();
foreach ($results as $result) {
	$tables[$result[0]] = $result[0];
}
$fields['tabel'] = new SelectField('tabel', null, 'Tabel', $tables);
$fields['btn'] = new FormButtons(CSR_ROOT, true, true, false);
$form = new Formulier(null, 'form', null);
$form->addFields($fields);
$form->titel = 'Dump database table';

if ($form->validate()) {
	DatabaseAdmin::instance()->sqlBackupTable($fields['tabel']->getValue());
} else {
	$view = new CsrLayoutPage($form);
	$view->view();
}
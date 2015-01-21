<?php

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_ADMIN')) {
	redirect(CSR_ROOT);
}

require_once 'model/framework/DatabaseAdmin.singleton.php';

$tables = array();
$results = DatabaseAdmin::instance()->sqlShowTables();
foreach ($results as $result) {
	$tables[$result[0]] = $result[0];
}
$fields['tabel'] = new SelectField('tabel', null, 'Tabel', $tables);
$fields['btn'] = new FormDefaultKnoppen('/', false);
$form = new Formulier(null, null);
$form->addFields($fields);
$form->titel = 'Dump database table';

if ($form->validate()) {
	DatabaseAdmin::instance()->sqlBackupTable($fields['tabel']->getValue());
} else {
	$pagina = new CsrLayoutPage($form);
	$pagina->view();
}
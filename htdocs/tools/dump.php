<?php

use CsrDelft\Orm\Persistence\DatabaseAdmin;

require_once 'configuratie.include.php';

if (!LoginModel::mag('P_ADMIN')) {
	redirect(CSR_ROOT);
}

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
	$name = $fields['tabel']->getValue();
	$filename = 'backup-' . $name . '_' . date('d-m-Y_H-i-s') . '.sql.gz';
	header('Content-Type: application/x-gzip');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	$cred = parse_ini_file(ETC_PATH . 'mysql.ini');
	$cmd = 'mysqldump --user=' . $cred['user'] . ' --password=' . $cred['pass'] . ' --host=' . $cred['host'] . ' ' . $cred['db'] . ' ' . $name . ' | gzip --best';
	passthru($cmd);
} else {
	$pagina = new CsrLayoutPage($form);
	$pagina->view();
}
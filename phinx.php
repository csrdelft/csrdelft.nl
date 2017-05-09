<?php
/**
 * Configuratie voor Phinx, verbind met de stek database om daar migraties op te kunnen runnen.
 */
// PHP from Phinx might not have a proper path
set_include_path(get_include_path() . PATH_SEPARATOR . 'lib');
require_once 'defines.include.php';

$db_cred = parse_ini_file(ETC_PATH . 'mysql.ini');

return [
	'paths' => [
		'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
		'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
	],

	'environments' => [
		'default_migration_table' => 'phinxlog',
		'default_database' => 'stekdb',
		'stekdb' => [
			'adapter' => 'mysql',
			'host' => $db_cred['host'],
			'name' => $db_cred['db'],
			'user' => $db_cred['user'],
			'pass' => $db_cred['pass']
		]
	],

	'version_order' => 'creation'
];
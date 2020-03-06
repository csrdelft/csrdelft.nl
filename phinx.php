<?php
/**
 * Configuratie voor Phinx, verbind met de stek database om daar migraties op te kunnen runnen.
 */

use Phinx\Migration\Manager\Environment;

require __DIR__ . '/config/bootstrap.php';

return [
	'paths' => [
		'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
		'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
	],

	'environments' => [
		'default_migration_table' => 'phinxlog',
		'default_database' => 'stekdb',
		// https://github.com/cakephp/phinx/issues/1706
		// Voer de dsn parse logica zelf uit.
		'stekdb' => (new Environment(null, [
			'dsn' => env('DATABASE_URL'),
		]))->getOptions()
	],

	'version_order' => 'creation'
];

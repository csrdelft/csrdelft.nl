<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviSaldoLog.class.php';

class CiviSaldoLogModel extends PersistenceModel {
	const ORM = CiviSaldoLog::class;
	const DIR = 'fiscaat/';

	protected static $instance;

	public function log($type, $data) {
		$logEntry = new CiviSaldoLog();
		// Don't use filter_input for $_SERVER when PHP runs through FastCGI:
		// https://bugs.php.net/bug.php?id=49184
		$logEntry->ip = filter_var($_SERVER['REMOTE_ADDR']);
		$logEntry->type = $type;
		$logEntry->data = json_encode($data);
		$this->create($logEntry);
	}
}

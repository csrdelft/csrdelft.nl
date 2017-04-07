<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviSaldoLog.class.php';

class CiviSaldoLogModel extends PersistenceModel {
	const ORM = CiviSaldoLog::class;
	const DIR = 'fiscaat/';

	protected static $instance;

	public function log($type, $data) {
		$logEntry = new CiviSaldoLog();
		$logEntry->timestamp = new DateTime();
		$logEntry->ip = $_SERVER['REMOTE_ADDR'];
		$logEntry->type = $type;
		$logEntry->data = $data;
	}
}

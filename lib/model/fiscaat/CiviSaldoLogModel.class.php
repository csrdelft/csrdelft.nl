<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviSaldoLog.class.php';

class CiviSaldoLogModel extends PersistenceModel {
	const ORM = CiviSaldoLog::class;
	const DIR = 'fiscaat/';

	protected static $instance;

	public function log($type, $data) {
		$logEntry = new CiviSaldoLog();
		$logEntry->timestamp = date_create()->getTimestamp();
		$logEntry->ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
		$logEntry->type = $type;
		$logEntry->data = json_encode($data);
		$this->create($logEntry);
	}
}

<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaal/CiviSaldoLog.class.php';

class CiviSaldoLogModel extends PersistenceModel {
	const ORM = CiviSaldoLog::class;
	const DIR = 'fiscaal/';

	protected static $instance;
}

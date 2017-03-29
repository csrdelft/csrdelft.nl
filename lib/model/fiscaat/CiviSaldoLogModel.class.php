<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviSaldoLog.class.php';

class CiviSaldoLogModel extends PersistenceModel {
	const ORM = CiviSaldoLog::class;
	const DIR = 'fiscaat/';

	protected static $instance;
}

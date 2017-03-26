<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaal/MaalcieLog.class.php';

class MaalcieLogModel extends PersistenceModel {
	const ORM = 'MaalcieLog';
	const DIR = 'fiscaal/';

	protected static $instance;
}

<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaal/CiviPrijs.class.php';

class CiviPrijsModel extends PersistenceModel {
	const ORM = CiviPrijs::class;
	const DIR = 'fiscaal/';

	protected static $instance;
}

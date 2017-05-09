<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviPrijs.class.php';

class CiviPrijsModel extends PersistenceModel {
	const ORM = CiviPrijs::class;
	const DIR = 'fiscaat/';

	protected static $instance;
}

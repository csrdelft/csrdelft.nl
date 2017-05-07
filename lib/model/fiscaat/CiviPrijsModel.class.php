<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviPrijs;
use CsrDelft\Orm\PersistenceModel;

class CiviPrijsModel extends PersistenceModel {
	const ORM = CiviPrijs::class;
	const DIR = 'fiscaat/';

	protected static $instance;
}

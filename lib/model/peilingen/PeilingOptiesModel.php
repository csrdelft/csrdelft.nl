<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\model\entity\peilingen\PeilingOptie;
use CsrDelft\Orm\PersistenceModel;

class PeilingOptiesModel extends PersistenceModel {

	const ORM = PeilingOptie::class;

	protected static $instance;

}

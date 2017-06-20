<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\model\entity\peilingen\PeilingStem;
use CsrDelft\Orm\PersistenceModel;

class PeilingStemmenModel extends PersistenceModel {

	const ORM = PeilingStem::class;

	protected static $instance;

	public function heeftGestemd($peiling_id, $uid) {
		return $this->existsByPrimaryKey(array($peiling_id, $uid));
	}

}

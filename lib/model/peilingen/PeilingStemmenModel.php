<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\model\entity\peilingen\PeilingStem;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class PeilingStemmenModel extends PersistenceModel {

	const ORM = PeilingStem::class;

	/**
	 * @param int $peiling_id
	 * @param string $uid
	 *
	 * @return bool
	 */
	public function heeftGestemd($peiling_id, $uid) {
		return $this->existsByPrimaryKey(array($peiling_id, $uid));
	}

}

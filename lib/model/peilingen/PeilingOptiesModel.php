<?php

namespace CsrDelft\model\peilingen;

use CsrDelft\model\entity\peilingen\PeilingOptie;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class PeilingOptiesModel extends PersistenceModel {
	const ORM = PeilingOptie::class;

	/**
	 * @param $id
	 * @return PeilingOptie|false
	 */
	public function getById($id) {
		return $this->retrieveByPrimaryKey([$id]);
	}

	/**
	 * Zie PeilingenLogic::getOptiesVoorPeiling
	 *
	 * @param $peilingId
	 * @return PeilingOptie[]
	 */
	public function getByPeilingId($peilingId) {
		return $this->find('peiling_id = ?', [$peilingId])->fetchAll();
	}
}

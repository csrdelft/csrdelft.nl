<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\Orm\PersistenceModel;
use CsrDelft\model\entity\bibliotheek\BiebRubriek;

/**
 * BiebRubriek.class.php  |  Gerrit Uitslag
 *
 * rubriek
 *
 */
class BiebRubriekModel extends PersistenceModel {

	const ORM = BiebRubriek::class;

	/**
	 * @param int $id
	 * @return BiebRubriek|false
	 */
	public function get(int $id) {
		return $this->retrieveByPrimaryKey([$id]);
	}



}

<?php

namespace CsrDelft\view\datatable;

use CsrDelft\Orm\Entity\PersistentEntity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */
class RemoveRowsResponse extends DataTableResponse {

	/**
	 * @param PersistentEntity $entity
	 * @return string
	 */
	public function getJson($entity) {
		return parent::getJson(array(
			'UUID' => $entity->getUUID(),
			'remove' => true
		));
	}

}

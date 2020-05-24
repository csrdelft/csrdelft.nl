<?php

namespace CsrDelft\view\datatable;

use CsrDelft\Orm\Entity\PersistentEntity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 07/05/2017
 */
class RemoveRowsResponse extends DataTableResponse {

	/**
	 * @param PersistentEntity $entity
	 */
	public function renderElement($entity) {
		return array(
			'UUID' => $entity->getUUID(),
			'remove' => true
		);
	}

}

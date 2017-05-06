<?php
/**
 * RemoveRowsResponse.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */

namespace CsrDelft\view\formulier\datatable;

class RemoveRowsResponse extends DataTableResponse {

	public function getJson($entity) {
		return parent::getJson(array(
			'UUID' => $entity->getUUID(),
			'remove' => true
		));
	}

}
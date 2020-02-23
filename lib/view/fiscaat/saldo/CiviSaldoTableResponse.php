<?php

namespace CsrDelft\view\fiscaat\saldo;

use CsrDelft\model\entity\fiscaat\CiviSaldo;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */
class CiviSaldoTableResponse extends DataTableResponse {
	/**
	 * @param CiviSaldo $entity
	 * @return string
	 */
	public function renderElement($entity) {
		return array(
			'UUID' => $entity->getUUID(),
			'id' => $entity->id,
			'uid' => $entity->uid,
			'naam' => ProfielRepository::existsUid($entity->uid) ? ProfielRepository::getNaam($entity->uid, 'volledig') : $entity->naam,
			'lichting' => substr($entity->uid, 0, 2),
			'saldo' => $entity->saldo,
			'laatst_veranderd' => $entity->laatst_veranderd,
			'deleted' => $entity->deleted
		);
	}
}

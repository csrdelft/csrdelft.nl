<?php

namespace CsrDelft\view\groepen;

use CsrDelft\model\entity\groepen\AbstractGroep;
use CsrDelft\model\entity\groepen\AbstractGroepLid;
use CsrDelft\view\JsonResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 09/07/2018
 *
 */
class GroepJsonResponse extends JsonResponse {
	/**
	 * @param AbstractGroep $entity
	 * @return string
	 */
	public function getJson($entity) {
		$data = [
			'id' => $entity->id,
			'naam' => $entity->naam,
			'familie' => $entity->familie,
			'begin_moment' => $entity->begin_moment,
			'eind_moment' => $entity->eind_moment,
			'status' => $entity->status,
			'samenvatting' => $entity->samenvatting,
			'omschrijving' => $entity->omschrijving,
			'keuzelijst' => $entity->keuzelijst,
			'maker_uid' => $entity->maker_uid,
			'UUID' => $entity->getUUID(),
			'leden' => array_map([$this, 'getLidJson'], $entity->getLeden()),
		];

		return parent::getJson($data);
	}

	protected function getLidJson(AbstractGroepLid $lid) {
		return [
			'uid' => $lid->uid,
			'door_uid' => $lid->door_uid,
			'opmerking' => $lid->opmerking,
			'lid_sinds' => $lid->lid_sinds,
		];
	}
}

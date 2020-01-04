<?php

namespace CsrDelft\view\fiscaat\producten;

use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\view\JsonLijstResponse;
use CsrDelft\view\JsonResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/04/2017
 */
class CiviProductSuggestiesResponse extends JsonLijstResponse {
	/**
	 * @param CiviProduct $entity
	 * @return array
	 */
	public function renderElement($entity) {
		return array(
			'url' => '/fiscaat/producten',
			'value' => $entity->getBeschrijvingFormatted(),
			'label' => $entity->id,
			'id' => $entity->id
		);
	}
}

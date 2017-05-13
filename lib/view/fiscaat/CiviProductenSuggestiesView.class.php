<?php

namespace CsrDelft\view\fiscaat;
use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\view\JsonLijstResponse;

/**
 * CiviProductenSuggestiesView.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/04/2017
 */
class CiviProductenSuggestiesView extends JsonLijstResponse {
	/**
	 * @param CiviProduct $entity
	 * @return string
	 */
	public function getJson($entity) {
		return json_encode(array(
			'url' => '/fiscaat/producten',
			'value' => $entity->getBeschrijvingFormatted(),
			'label' => $entity->id,
			'id' => $entity->id
		));
	}
}

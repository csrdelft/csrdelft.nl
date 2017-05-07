<?php

namespace CsrDelft\view\fiscaat;
use CsrDelft\model\entity\fiscaat\CiviCategorie;
use CsrDelft\view\JsonLijstResponse;

/**
 * CiviProductenSuggestiesView.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/04/2017
 */
class CiviCategorieSuggestiesView extends JsonLijstResponse {
	/**
	 * @param CiviCategorie $entity
	 * @return string
	 */
	public function getJson($entity) {
		return json_encode(array(
			'url' => '/fiscaat/categorien',
			'value' => $entity->type,
			'label' => $entity->id,
			'id' => $entity->id
		));
	}
}

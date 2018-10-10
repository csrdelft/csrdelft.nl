<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\view\JsonLijstResponse;

/**
 * Typeahead response voor EetplanBekendeHuizenForm op /eetplan/bekendehuizen/zoeken
 *
 * Class EetplanHuizenResponse
 */
class EetplanHuizenZoekenResponse extends JsonLijstResponse {

	/**
	 * @param Woonoord $entity
	 *
	 * @return string
	 */
	public function getJson($entity) {
		return parent::getJson(array(
			'url' => $entity->getUrl(),
			'label' => $entity->id,
			'value' => $entity->naam,
			'id' => $entity->id,
		));
	}
}

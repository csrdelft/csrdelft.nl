<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\view\JsonLijstResponse;

/**
 * Typeahead response voor EetplanBekendeHuizenForm op /eetplan/bekendehuizen/zoeken
 *
 * Class EetplanHuizenResponse
 */
class EetplanHuizenZoekenResponse extends JsonLijstResponse
{

	/**
	 * @param Woonoord $entity
	 *
	 */
	public function renderElement($entity)
	{
		return array(
			'url' => $entity->getUrl(),
			'label' => $entity->id,
			'value' => $entity->naam,
			'id' => $entity->id,
		);
	}
}

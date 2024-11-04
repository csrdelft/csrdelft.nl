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
	 * @return (int|string)[]
	 *
	 * @psalm-return array{url: string, label: int, value: string, id: int}
	 */
	public function renderElement($entity)
	{
		return [
			'url' => $entity->getUrl(),
			'label' => $entity->id,
			'value' => $entity->naam,
			'id' => $entity->id,
		];
	}
}

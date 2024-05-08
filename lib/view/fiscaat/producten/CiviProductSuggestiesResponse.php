<?php

namespace CsrDelft\view\fiscaat\producten;

use CsrDelft\entity\fiscaat\CiviProduct;
use CsrDelft\view\JsonLijstResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 04/04/2017
 */
class CiviProductSuggestiesResponse extends JsonLijstResponse
{
	/**
	 * @param CiviProduct $entity
	 * @return array
	 */
	public function renderElement($entity): array
	{
		return [
			'url' => '/fiscaat/producten',
			'value' => $entity->getBeschrijvingFormatted(),
			'label' => $entity->id,
			'id' => $entity->id,
		];
	}
}

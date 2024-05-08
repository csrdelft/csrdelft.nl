<?php

namespace CsrDelft\view\fiscaat;

use CsrDelft\entity\fiscaat\CiviCategorie;
use CsrDelft\view\JsonLijstResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 04/04/2017
 */
class CiviCategorieSuggestiesResponse extends JsonLijstResponse
{
	/**
	 * @param CiviCategorie $entity
	 * @return array
	 */
	public function renderElement($entity): array
	{
		return [
			'url' => '/fiscaat/categorien',
			'value' => $entity->getWeergave(),
			'label' => $entity->getWeergave(),
			'id' => $entity->getId(),
		];
	}
}

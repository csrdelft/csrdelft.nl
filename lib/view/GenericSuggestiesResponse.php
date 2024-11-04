<?php

namespace CsrDelft\view;

use CsrDelft\view\formulier\DisplayEntity;

class GenericSuggestiesResponse extends JsonLijstResponse
{
	/**
	 * @param DisplayEntity $entity
	 *
	 * @return (mixed|string)[]
	 *
	 * @psalm-return array{value: string, label: mixed, id: mixed}
	 */
	public function renderElement($entity)
	{
		return [
			'value' => $entity->getWeergave(),
			'label' => $entity->getId(),
			'id' => $entity->getId(),
		];
	}
}

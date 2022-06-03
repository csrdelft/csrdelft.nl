<?php

namespace CsrDelft\view;

use CsrDelft\view\formulier\DisplayEntity;

class GenericSuggestiesResponse extends JsonLijstResponse {
	/**
	 * @param DisplayEntity $entity
	 * @return array
	 */
	public function renderElement($entity) {
		return [
			'value' => $entity->getWeergave(),
			'label' => $entity->getId(),
			'id' => $entity->getId(),
		];
	}
}

<?php

/**
 * CiviProductenSuggestiesView.class.php
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/04/2017
 */
class CiviProductenSuggestiesView extends JsonLijstResponse {
	public function getJson($entity) {
		return json_encode(array(
			'url' => '/fiscaat/producten',
			'label' => $entity->beschrijving,
			'value' => $entity->id
		));
	}
}
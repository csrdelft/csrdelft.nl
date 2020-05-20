<?php

namespace CsrDelft\view\fiscaat\bestellingen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\repository\fiscaat\CiviBestellingInhoudRepository;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/02/2018
 */
class CiviBestellingInhoudTableResponse extends DataTableResponse {
	/**
	 * @param CiviBestellingInhoud $entity
	 * @return array
	 */
	public function renderElement($entity) {
		return [
			'bestelling_id' => $entity->bestelling_id,
			'product_id' => $entity->product_id,
			'aantal' => $entity->aantal,
			'stukprijs' => sprintf('€%.2f', $entity->product->tmpPrijs / 100),
			'totaalprijs' => sprintf('€%.2f', $entity->getPrijs() / 100),
			'product' => $entity->product->beschrijving,
		];
	}
}

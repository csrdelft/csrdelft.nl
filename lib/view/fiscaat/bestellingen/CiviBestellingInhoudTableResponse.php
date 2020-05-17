<?php

namespace CsrDelft\view\fiscaat\bestellingen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\model\fiscaat\CiviBestellingInhoudModel;
use CsrDelft\repository\fiscaat\CiviProductRepository;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/02/2018
 */
class CiviBestellingInhoudTableResponse extends DataTableResponse {
	/**
	 * @param CiviBestellingInhoud $entity
	 * @return string
	 */
	public function renderElement($entity) {
		$civiProduct = ContainerFacade::getContainer()->get(CiviProductRepository::class)->getProduct($entity->product_id);
		return [
			'bestelling_id' => $entity->bestelling_id,
			'product_id' => $entity->product_id,
			'aantal' => $entity->aantal,
			'stukprijs' => sprintf('€%.2f', $civiProduct->prijs / 100),
			'totaalprijs' => sprintf('€%.2f', ContainerFacade::getContainer()->get(CiviBestellingInhoudModel::class)->getPrijs($entity) / 100),
			'product' => $civiProduct->beschrijving,
		];
	}
}

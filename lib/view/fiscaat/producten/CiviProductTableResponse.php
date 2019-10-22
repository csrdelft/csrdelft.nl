<?php

namespace CsrDelft\view\fiscaat\producten;

use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\model\fiscaat\CiviCategorieModel;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */
class CiviProductTableResponse extends DataTableResponse {
	/**
	 * @param CiviProduct $entity
	 * @return string
	 */
	public function renderElement($entity) {
		return [
			'UUID' => $entity->getUUID(),
			'id' => $entity->id,
			'status' => $entity->status,
			'beschrijving' => $entity->beschrijving,
			'beheer' => $entity->beheer,
			'categorie' => CiviCategorieModel::get($entity->categorie_id)->getBeschrijving(),
			'prijs' => $entity->prijs,
			'prioriteit' => $entity->prioriteit,
		];
	}
}

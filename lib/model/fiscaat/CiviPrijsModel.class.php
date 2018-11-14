<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\common\CsrException;
use CsrDelft\model\entity\fiscaat\CiviPrijs;
use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviPrijsModel extends PersistenceModel {
	/**
	 * ORM class.
	 */
	const ORM = CiviPrijs::class;

	/**
	 * Verwijderd alle prijzen voor een product zonder klagen. PAS DUS OP.
	 *
	 * Moet in een transaction aangeroepen worden.
	 *
	 * @param CiviProduct $product
	 */
	public function verwijderVoorProduct(CiviProduct $product) {
		if (!Database::instance()->getDatabase()->inTransaction()) throw new CsrException('Kan geen product verwijderen als je niet in een transactie zit!');

		$prijzen = $this->find('product_id = ?', [$product->id]);

		foreach ($prijzen as $prijs) {
			$this->delete($prijs);
		}
	}
}

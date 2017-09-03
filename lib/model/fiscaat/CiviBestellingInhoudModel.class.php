<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\Orm\PersistenceModel;

/**
 * @author Gerben Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class CiviBestellingInhoudModel extends PersistenceModel {
	const ORM = CiviBestellingInhoud::class;

	/**
	 * @var CiviBestellingInhoudModel
	 */
	protected static $instance;

	/**
	 * @param CiviBestellingInhoud $inhoud
	 *
	 * @return int
	 */
	public function getPrijs(CiviBestellingInhoud $inhoud) {
		$product = CiviProductModel::instance()->getProduct($inhoud->product_id);

		return $product->prijs * $inhoud->aantal;
	}

	/**
	 * @param CiviBestellingInhoud $inhoud
	 *
	 * @return string
	 */
	public function getBeschrijving(CiviBestellingInhoud $inhoud) {
		$product = CiviProductModel::instance()->getProduct($inhoud->product_id);
		return sprintf("%d %s", $inhoud->aantal, $product->beschrijving);
	}
}

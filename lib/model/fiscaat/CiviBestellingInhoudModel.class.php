<?php

namespace CsrDelft\model\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviBestellingInhoud;
use CsrDelft\Orm\PersistenceModel;

class CiviBestellingInhoudModel extends PersistenceModel {
	const ORM = CiviBestellingInhoud::class;
	const DIR = 'fiscaat/';

	protected static $instance;

	public function getPrijs(CiviBestellingInhoud $inhoud) {
		$product = CiviProductModel::instance()->getProduct($inhoud->product_id);

		return $product->prijs * $inhoud->aantal;
	}

	public function getBeschrijving(CiviBestellingInhoud $inhoud) {
		$product = CiviProductModel::instance()->getProduct($inhoud->product_id);
		return sprintf("%d %s", $inhoud->aantal, $product->beschrijving);
	}
}

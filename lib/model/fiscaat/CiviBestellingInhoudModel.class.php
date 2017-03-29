<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaat/CiviBestellingInhoud.class.php';
require_once 'model/fiscaat/MaalcieProductModel.class.php';

class CiviBestellingInhoudModel extends PersistenceModel {
	const ORM = 'CiviBestellingInhoud';
	const DIR = 'fiscaat/';

	protected static $instance;

	public function getPrijs(CiviBestellingInhoud $inhoud) {
		$product = CiviProductModel::instance()->getProduct($inhoud->productid);

		return $product->prijs * $inhoud->aantal;
	}
}

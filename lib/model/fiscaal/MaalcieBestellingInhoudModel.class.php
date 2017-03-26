<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaal/MaalcieBestellingInhoud.class.php';
require_once 'model/fiscaal/MaalcieProductModel.class.php';

class MaalcieBestellingInhoudModel extends PersistenceModel {
	const ORM = 'MaalcieBestellingInhoud';
	const DIR = 'fiscaal/';

	protected static $instance;

	public function getPrijs(MaalcieBestellingInhoud $inhoud) {
		$product = MaalcieProductModel::instance()->getProduct($inhoud->productid);

		return $product->prijs * $inhoud->aantal;
	}
}

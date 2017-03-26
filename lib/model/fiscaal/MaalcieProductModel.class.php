<?php

use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/fiscaal/MaalcieProduct.class.php';
require_once 'model/fiscaal/MaalciePrijsModel.class.php';

class MaalcieProductModel extends PersistenceModel {
	const ORM = 'MaalcieProduct';
	const DIR = 'fiscaal/';

	protected static $instance;

	public function getProduct($id) {
		/** @var MaalcieProduct $product */
		$product = $this->retrieveByPrimaryKey(array($id));

		$product->prijs = MaalciePrijsModel::instance()
			->find('productid = ?', array($id), null, 'van DESC', 1)->fetch()->prijs;

		return $product;
	}
}

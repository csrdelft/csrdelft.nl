<?php

require_once 'model/entity/fiscaal/MaalcieProduct.class.php';

class MaalcieProductModel extends PersistenceModel {
	const ORM = 'MaalcieProduct';
	const DIR = 'fiscaal/';

	public function getProduct($id) {
		/** @var MaalcieProduct $product */
		$product = $this->retrieveByPrimaryKey(array($id));

		$product->prijs = MaalciePrijsModel::instance()
			->find('productid = ?', array($id), null, 'van DESC', 1);

		return $product;
	}
}

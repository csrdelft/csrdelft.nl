<?php

/**
 * StreepLijstProductenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class StreepLijstProductenModel extends PersistenceModel {

	const ORM = 'StreepLijstProduct';
	const DIR = 'betalen/';

	protected static $instance;

	public function maakStreepLijstProduct($product_id, $streeplijst_id) {
		$streeplijst_product = new StreepLijstProduct();
		$streeplijst_product->product_id = $product_id;
		$streeplijst_product->streeplijst_id = $streeplijst_id;
		$this->create($streeplijst_product);
		return $streeplijst_product;
	}

}

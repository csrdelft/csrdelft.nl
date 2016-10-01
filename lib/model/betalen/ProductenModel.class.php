<?php

/**
 * ProductenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProductenModel extends PersistenceModel {

	const ORM = 'Product';
	const DIR = 'betalen/';

	protected static $instance;

	public function newProduct($categorie_id, $naam, $beschrijving = null, $aantal_voorraad = null, $uitverkocht_moment = null) {
		$product = new Product();
		$product->categorie_id = $categorie_id;
		$product->naam = $naam;
		$product->beschrijving = $beschrijving;
		$product->aantal_voorraad = $aantal_voorraad;
		$product->uitverkocht_moment = $uitverkocht_moment;
		return $product;
	}

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity $product
	 * @return int product_id
	 */
	public function create(PersistentEntity $product) {
		$product->product_id = (int) parent::create($product);
		return $product->product_id;
	}

}

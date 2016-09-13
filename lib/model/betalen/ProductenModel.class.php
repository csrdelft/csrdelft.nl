<?php

/**
 * ProductenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProductenModel extends PersistenceModel {

	const ORM = 'Product';

	protected static $instance;

	public function maakProduct($categorie_id, $naam, $beschrijving = null, $aantal_voorraad = null, $uitverkocht_moment = null) {
		$product = new Product();
		$product->categorie_id = $categorie_id;
		$product->naam = $naam;
		$product->beschrijving = $beschrijving;
		$product->aantal_voorraad = $aantal_voorraad;
		$product->uitverkocht_moment = $uitverkocht_moment;
		$product->product_id = (int) $this->create($product);
		return $product;
	}

}

<?php

/**
 * ProductPrijzenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProductPrijzen extends PersistenceModel {

	const ORM = 'ProductPrijs';
	const DIR = 'betalen/';

	protected static $instance;

	public function maakProductPrijs($prijslijst_id, $product_id, $bedrag, $begin_moment, $eind_moment = null) {
		$prijs = new ProductPrijs();
		$prijs->prijslijst_id = $prijslijst_id;
		$prijs->product_id = $product_id;
		$prijs->bedrag = $bedrag;
		$prijs->begin_moment = $begin_moment;
		$prijs->eind_moment = $eind_moment;
		$prijs->prijs_id = (int) $this->create($prijs);
		return $prijs;
	}

}

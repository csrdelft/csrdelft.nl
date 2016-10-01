<?php

/**
 * ProductPrijsLijstenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProductPrijsLijstenModel extends PersistenceModel {

	const ORM = 'ProductPrijsLijst';
	const DIR = 'betalen/';

	protected static $instance;

	public function newProductPrijsLijst($titel) {
		$prijslijst = new ProductPrijsLijst();
		$prijslijst->titel = $titel;
		return $prijslijst;
	}

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity $prijslijst
	 * @return int prijslijst_id
	 */
	public function create(PersistentEntity $prijslijst) {
		$prijslijst->prijslijst_id = (int) parent::create($prijslijst);
		return $prijslijst->prijslijst_id;
	}

}

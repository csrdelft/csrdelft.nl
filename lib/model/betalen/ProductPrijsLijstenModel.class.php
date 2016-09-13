<?php

/**
 * ProductPrijsLijstenModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class ProductPrijsLijstenModel extends PersistenceModel {

	const ORM = 'ProductPrijsLijst';

	protected static $instance;

	public function maakProductPrijsLijst($titel) {
		$prijslijst = new ProductPrijsLijst();
		$prijslijst->titel = $titel;
		$prijslijst->prijslijst_id = (int) $this->create($prijslijst);
		return $prijslijst;
	}

}

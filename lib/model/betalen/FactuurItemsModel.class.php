<?php

/**
 * FactuurItemsModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class FactuurItemsModel extends PersistenceModel {

	const ORM = 'FactuurItem';
	const DIR = 'betalen/';

	protected static $instance;

	public function newFactuurItem($factuur_id, $titel, $prijs_per_stuk, $aantal = 1) {
		$item = new FactuurItem();
		$item->factuur_id = $factuur_id;
		$item->titel = $titel;
		$item->prijs_per_stuk = $prijs_per_stuk;
		$item->aantal = $aantal;
		return $item;
	}

	/**
	 * Set primary key.
	 *
	 * @param PersistentEntity $item
	 * @return int item_id
	 */
	public function create(PersistentEntity $item) {
		$item->item_id = (int) parent::create($item);
		return $item->item_id;
	}

}

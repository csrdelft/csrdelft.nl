<?php

require_once 'MVC/model/happie/MenukaartGroepenModel.class.php';

/**
 * MenukaartItemsModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Menukaart items CRUD.
 * 
 */
class HappieMenukaartItemsModel extends CachedPersistenceModel {

	const orm = 'HappieMenukaartItem';

	protected static $instance;

	protected function __construct() {
		parent::__construct('happie/');
	}

	public function getItem($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newItem() {
		$item = new HappieMenuKaartItem();
		$item->menukaart_groep = 0;
		$item->naam = '';
		$item->beschrijving = '';
		$item->allergie_info = '';
		$item->prijs = 0;
		$item->aantal_beschikbaar = 0;
		return $item;
	}

	public function create(PersistentEntity $item) {
		$item->item_id = parent::create($item);
		return $item;
	}

	public function getGroepItems(HappieMenukaartGroep $groep) {
		return $this->prefetch('menukaart_groep = ?', array($groep->groep_id));
	}

	public function getMenukaart() {
		// prefetch groepen en items
		$groepen = HappieMenukaartGroepenModel::instance()->prefetch();
		$items = group_by('menukaart_groep', HappieMenukaartItemsModel::instance()->prefetch());

		foreach ($groepen as $groep) {
			// set prefetched items
			if (!isset($items[$groep->groep_id])) {
				continue;
			}
			$groep->setItems($items[$groep->groep_id]);
		}
		return $groepen;
	}

}

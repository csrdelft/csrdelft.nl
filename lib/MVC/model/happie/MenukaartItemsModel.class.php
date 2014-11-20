<?php

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
		$this->default_order = 'menukaart_groep ASC, naam ASC';
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
		$groepen = group_by('gang', HappieMenukaartGroepenModel::instance()->prefetch());
		$items = group_by('menukaart_groep', HappieMenukaartItemsModel::instance()->prefetch());

		$menukaart = array();
		foreach (HappieGang::getTypeOptions() as $gang) {
			if (!isset($groepen[$gang])) {
				$menukaart[$gang] = array();
				continue;
			}
			foreach ($groepen[$gang] as $groep) {
				$menukaart[$gang][$groep->groep_id] = $groep;
				if (isset($items[$groep->groep_id])) {
					$groep->setItems($items[$groep->groep_id]);
				}
			}
		}
		return $menukaart;
	}

}

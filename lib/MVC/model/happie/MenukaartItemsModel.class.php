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
class HappieMenukaartItemsModel extends PersistenceModel {

	const orm = 'HappieMenukaartItem';

	protected static $instance;

	protected function __construct() {
		parent::__construct('happie/');
	}

	public function getItem($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newItem(HappieMenukaartGroep $groep, $naam, $beschrijving, $allergie_info, $prijs, $aantal_beschikbaar, $alcohol_leeftijd) {
		$item = new HappieMenuKaartItem();
		$item->groep_id = $groep->groep_id;
		$item->naam = $naam;
		$item->beschrijving = $beschrijving;
		$item->allergie_info = $allergie_info;
		$item->prijs = $prijs;
		$item->aantal_beschikbaar = $aantal_beschikbaar;
		$item->alcohol_leeftijd = $alcohol_leeftijd;
		$item->item_id = $this->create($item);
		return $item;
	}

}

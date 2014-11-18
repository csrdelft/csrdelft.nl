<?php

require_once 'MVC/model/happie/MenukaartItemsModel.class.php';

/**
 * BestellingenModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Bestellingen CRUD.
 * 
 */
class HappieBestellingenModel extends CachedPersistenceModel {

	const orm = 'HappieBestelling';

	protected static $instance;

	protected function __construct() {
		parent::__construct('happie/');
	}

	public function getBestelling($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

	public function newBestelling($tafel, HappieMenukaartItem $item, $aantal, $serveer_status, $financien_status, $klant_allergie = null) {
		$bestelling = new HappieMenuKaartItem();
		$bestelling->moment_nieuw = getDateTime();
		$bestelling->laatst_gewijzigd = null;
		$bestelling->wijzig_historie = '';
		$bestelling->tafel = $tafel;
		$bestelling->menukaart_item = $item->item_id;
		$bestelling->aantal = $aantal;
		$bestelling->serveer_status = $serveer_status;
		$bestelling->financien_status = $financien_status;
		$bestelling->klant_allergie = $klant_allergie;
		$bestelling->bestelling_id = $this->create($bestelling);
		return $bestelling;
	}

	public function update(PersistentEntity $bestelling) {
		$backup = $this->getBestelling($bestelling->bestelling_id);
		$bestelling->wijzig_historie .= json_encode($backup);
		$bestelling->laatst_gewijzigd = getDateTime();
		return parent::update($bestelling);
	}

}

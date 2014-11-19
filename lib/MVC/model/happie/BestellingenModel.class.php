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

	public function newBestelling($tafel, $item_id, $aantal, $opmerking = null) {
		$bestelling = new HappieBestelling();
		$bestelling->datum = date('Y-m-d');
		$bestelling->laatst_gewijzigd = getDateTime();
		$bestelling->wijzig_historie = '';
		$bestelling->tafel = $tafel;
		$bestelling->menukaart_item = $item_id;
		$bestelling->aantal = $aantal;
		$bestelling->serveer_status = HappieServeerStatus::Nieuw;
		$bestelling->financien_status = HappieFinancienStatus::Nieuw;
		$bestelling->opmerking = $opmerking;
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

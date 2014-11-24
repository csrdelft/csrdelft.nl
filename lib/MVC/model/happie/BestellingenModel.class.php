<?php

require_once 'MVC/model/happie/StatistiekModel.class.php';

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
		//$this->default_order = 'datum ASC, menukaart_item ASC, bestelling_id ASC';
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
		$bestelling->aantal_geserveerd = 0;
		$bestelling->serveer_status = HappieServeerStatus::Nieuw;
		$bestelling->financien_status = HappieFinancienStatus::Nieuw;
		$bestelling->opmerking = $opmerking;
		$bestelling->bestelling_id = $this->create($bestelling);
		// start serveer status log
		HappieStatistiekModel::instance()->log($bestelling->bestelling_id, 'serveer_status', null, $bestelling->serveer_status);
		return $bestelling;
	}

	public function update(PersistentEntity $bestelling) {
		// backup oude gegevens
		$backup = $this->getBestelling($bestelling->bestelling_id);
		$backup->wijzig_historie = null;
		$bestelling->wijzig_historie .= json_encode(array(getDateTime() => $backup)) . ",\n";

		// markeer wijziging bestelling
		if ($backup->tafel != $bestelling->tafel
				OR $backup->menukaart_item != $bestelling->menukaart_item
				OR $backup->aantal != $bestelling->aantal
				OR $backup->opmerking != $bestelling->opmerking
		) {
			$bestelling->laatst_gewijzigd = getDateTime();
		}

		// update serveer status log
		if ($backup->serveer_status != $bestelling->serveer_status) {
			HappieStatistiekModel::instance()->log($bestelling->bestelling_id, 'serveer_status', $backup->serveer_status, $bestelling->serveer_status);
		}

		return parent::update($bestelling);
	}

}

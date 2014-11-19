<?php

require_once 'MVC/model/entity/happie/HappieServeerStatus.enum.php';
require_once 'MVC/model/entity/happie/HappieFinancienStatus.enum.php';
require_once 'MVC/model/happie/MenukaartItemsModel.class.php';

/**
 * Bestelling.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een bestelling is een menukaart item dat besteld is in een bepaalde hoeveelheid, dus GEEN groep/verzameling!
 * Per bestelling een serveer-status en financien-status.
 * 
 */
class HappieBestelling extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $bestelling_id;
	/**
	 * Datum van bestelling
	 * @var string
	 */
	public $datum;
	/**
	 * Datum en tijd van wijzigen
	 * @var string
	 */
	public $laatst_gewijzigd;
	/**
	 * Log van wijzigingen
	 * @var string
	 */
	public $wijzig_historie;
	/**
	 * Tafel
	 * @var int
	 */
	public $tafel;
	/**
	 * MenukaartItem
	 * @var int
	 */
	public $menukaart_item;
	/**
	 * Aantal van MenukaartItem
	 * @var int
	 */
	public $aantal;
	/**
	 * Aantal geserveerd
	 * @var int
	 */
	public $aantal_geserveerd;
	/**
	 * Serveer-status
	 * @var HappieServeerStatus
	 */
	public $serveer_status;
	/**
	 * Financien-status
	 * @var HappieFinancienStatus
	 */
	public $financien_status;
	/**
	 * Allergie informatie over klant bijv.
	 * @var string
	 */
	public $opmerking;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'bestelling_id'		 => array(T::Integer, false, 'auto_increment'),
		'datum'				 => array(T::Date),
		'laatst_gewijzigd'	 => array(T::DateTime),
		'wijzig_historie'	 => array(T::Text),
		'tafel'				 => array(T::Integer),
		'menukaart_item'	 => array(T::Integer),
		'aantal'			 => array(T::Integer),
		'aantal_geserveerd'	 => array(T::Integer),
		'serveer_status'	 => array(T::Enumeration, false, 'HappieServeerStatus'),
		'financien_status'	 => array(T::Enumeration, false, 'HappieFinancienStatus'),
		'opmerking'			 => array(T::Text, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('bestelling_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'happie_bestellingen';

	public function jsonSerialize() {
		$array = parent::jsonSerialize();
		$item = $this->getItem($this->menukaart_item);
		if ($item) {
			$array['menukaart_item'] = $item->naam;
			$groep = $item->getGroep();
			if ($groep) {
				$array['menu_groep'] = $groep->naam;
			}
		}
		$array['tafel'] = 'Tafel ' . $this->tafel;
		$array['laatst_gewijzigd'] = reldate($this->laatst_gewijzigd);
		return $array;
	}

	public function getItem() {
		return HappieMenukaartItemsModel::instance()->getItem($this->menukaart_item);
	}

}

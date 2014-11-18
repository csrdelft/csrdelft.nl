<?php

require_once 'MVC/model/entity/happie/HappieServeerStatus.enum.php';
require_once 'MVC/model/entity/happie/HappieFinancienStatus.enum.php';

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
	 * Datum en tijd van bestelling
	 * @var string
	 */
	public $moment_nieuw;
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
	 * Aantal van dit product
	 * @var int
	 */
	public $aantal;
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
	 * Allergie informatie over klant
	 * @var string
	 */
	public $klant_allergie;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'bestelling_id'		 => array(T::Integer, false, 'auto_increment'),
		'moment_nieuw'		 => array(T::DateTime),
		'laatst_gewijzigd'	 => array(T::DateTime, true),
		'wijzig_historie'	 => array(T::Text),
		'tafel'				 => array(T::Integer),
		'menukaart_item'	 => array(T::Integer),
		'aantal'			 => array(T::Integer),
		'serveer_status'	 => array(T::Enumeration, false, 'HappieServeerStatus'),
		'financien_status'	 => array(T::Enumeration, false, 'HappieFinancienStatus'),
		'klant_allergie'	 => array(T::String, true)
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

}

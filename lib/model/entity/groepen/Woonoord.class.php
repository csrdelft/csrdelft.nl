<?php

require_once 'model/entity/groepen/HuisStatus.enum.php';

/**
 * Woonoord.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een woonoord is waar C.S.R.-ers bij elkaar wonen.
 * 
 */
class Woonoord extends Groep {

	const leden = 'BewonersModel';

	/**
	 * Woonoord / Huis
	 * @var HuisStatus
	 */
	public $huis_status;
	/**
	 * Veranderingen van status
	 * @var string
	 */
	public $status_historie;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'huis_status'		 => array(T::Enumeration, false, 'HuisStatus'),
		'status_historie'	 => array(T::Text)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'woonoorden';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	public function getUrl() {
		return '/groepen/woonoorden/' . $this->id . '/';
	}

}

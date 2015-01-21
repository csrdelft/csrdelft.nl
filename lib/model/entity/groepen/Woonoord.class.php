<?php

/**
 * Woonoord.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een woonoord is waar C.S.R.-ers bij elkaar wonen.
 * 
 */
class Woonoord extends Groep {

	/**
	 * Woonoord / Huis
	 * @var HuisStatus
	 */
	public $status;
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
		'status'			 => array(T::Enumeration, false, 'HuisStatus'),
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

}

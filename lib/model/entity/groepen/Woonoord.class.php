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
	 * Veranderingen van huisstatus
	 * @var string
	 */
	public $status_historie;
	/**
	 * woonoord / huis
	 * @see HuisStatus
	 * @var string
	 */
	public $huis_status;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'status_historie'	 => array(T::Text, true),
		'huis_status'		 => array(T::String, true)
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

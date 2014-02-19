<?php

require_once 'MCV/model/entity/Groep.abstract.php';

Ketzer::__constructStatic();

/**
 * Ketzer.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een ketzer is een aanmeldbare groep.
 * 
 */
class Ketzer extends Groep {

	/**
	 * Extend the persistent fields with those of the base class.
	 */
	public static function __constructStatic() {
		self::$persistent_fields = parent::$persistent_fields + self::$persistent_fields;
	}

	/**
	 * Type van groep
	 * @var string
	 */
	public static $class_name = 'Ketzer';
	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 */
	public $aanmeld_filter;
	/**
	 * Datum en tijd van toevoegen
	 * @var string
	 */
	public $aanmeld_limiet;
	/**
	 * Mogelijke opties bij aanmelden
	 * @var string
	 */
	public $aanmeld_opties;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'aanmeld_filter' => 'varchar(255) DEFAULT NULL',
		'aanmeld_limiet' => 'int(11) DEFAULT NULL',
		'aanmeld_opties' => 'text DEFAULT NULL'
	);

}

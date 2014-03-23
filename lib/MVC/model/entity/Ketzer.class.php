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
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzers';

	/**
	 * Extend the persistent fields.
	 */
	public static function __constructStatic() {
		self::$persistent_fields = parent::$persistent_fields + self::$persistent_fields;
	}

}

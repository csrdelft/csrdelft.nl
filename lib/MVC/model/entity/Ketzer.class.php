<?php

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
	 * Maximaal aantal groepsleden
	 * @var string
	 */
	public $aanmeld_limiet;
	/**
	 * Datum en tijd aanmeldperiode begin
	 * @var string
	 */
	public $aanmelden_vanaf;
	/**
	 * Datum en tijd aanmeldperiode einde
	 * @var string
	 */
	public $aanmelden_tot;
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
		'aanmeld_limiet' => 'int(11) DEFAULT NULL',
		'aanmelden_vanaf' => 'datetime NOT NULL',
		'aanmelden_tot' => 'datetime NOT NULL',
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

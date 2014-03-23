<?php

require_once 'MCV/model/entity/Groep.abstract.php';

Commissie::__constructStatic();

/**
 * Commissie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een commissie is een groep die een status heeft en
 * waarvan de groepsleden een functie hebben.
 * 
 */
class Commissie extends Groep {

	/**
	 * Status: ot / ht / ft
	 * @var string
	 */
	public $status = 'ht';
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'status' => 'varchar(2) DEFAULT NULL'
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'commissies';

	/**
	 * Extend the persistent fields.
	 */
	public static function __constructStatic() {
		self::$persistent_fields = parent::$persistent_fields + self::$persistent_fields;
	}

}

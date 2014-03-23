<?php

require_once 'MCV/model/entity/Commissie.class.php';

Bestuur::__constructStatic();

/**
 * Bestuur.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een bestuur is een speciaal type van een commissie.
 * 
 */
class Bestuur extends Commissie {

	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'besturen';

	/**
	 * Extend the persistent fields.
	 */
	public static function __constructStatic() {
		self::$persistent_fields = parent::$persistent_fields + self::$persistent_fields;
	}

}

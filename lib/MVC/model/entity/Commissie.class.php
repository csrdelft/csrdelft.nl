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
	 * Extend the persistent fields with those of the base class.
	 */
	public static function __constructStatic() {
		self::$persistent_fields = parent::$persistent_fields + self::$persistent_fields;
	}

	/**
	 * Type van groep
	 * @var string
	 */
	public $class_name = 'Commissie';
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

}

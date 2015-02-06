<?php

require_once 'model/entity/groepen/LidStatus.enum.php';

/**
 * LichtingsLid.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een lid van een lichting.
 * 
 */
class LichtingsLid extends GroepLid {

	/**
	 * Verticaan uid
	 * @var string
	 */
	public $lidafdatum;
	/**
	 * Verticaan uid
	 * @var string
	 */
	public $lidstatus;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'lidafdatum' => array(T::Date),
		'lidstatus'	 => array(T::Enumeration, false, 'LidStatus'),
	);
	protected static $table_name = 'lichting_leden';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

}

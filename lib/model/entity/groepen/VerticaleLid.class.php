<?php

/**
 * VerticaleLid.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een lid van een verticale.
 * 
 */
class VerticaleLid extends GroepLid {

	/**
	 * Verticaleleider
	 * @var boolean
	 */
	public $leider;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'leider' => array(T::Boolean)
	);
	protected static $table_name = 'verticale_leden';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

}

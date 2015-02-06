<?php

/**
 * Lichting.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Lichting extends Groep {

	/**
	 * Primary key
	 * @var string
	 */
	public $lidjaar;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'lidjaar' => array(T::Integer)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'lichtingen';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	public function getUrl() {
		return '/groepen/lichtingen/' . $this->lidjaar . '/';
	}

}

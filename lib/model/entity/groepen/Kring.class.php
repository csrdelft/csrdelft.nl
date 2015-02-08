<?php

/**
 * Kring.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Kring extends Groep {

	const leden = 'KringLedenModel';

	/**
	 * Verticaleletter
	 * @var string
	 */
	public $verticale;
	/**
	 * Kringnummer
	 * @var int
	 */
	public $kring_nummer;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'verticale'		 => array(T::Char),
		'kring_nummer'	 => array(T::Integer)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'kringen';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	public function getUrl() {
		return '/groepen/kringen/' . $this->verticale . '.' . $this->kring_nummer . '/';
	}

}

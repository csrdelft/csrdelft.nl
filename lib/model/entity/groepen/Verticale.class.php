<?php

/**
 * Verticale.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Verticale extends Groep {

	const leden = 'VerticaleLedenModel';

	/**
	 * Primary key
	 * @var string
	 */
	public $letter;
	/**
	 * Kringcoach uid
	 * @var array
	 */
	public $kringcoach;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'letter'	 => array(T::Char),
		'kringcoach' => array(T::UID)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'verticalen';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	public function getUrl() {
		return '/groepen/verticalen/' . $this->letter . '/';
	}

	public function getKringen() {
		return KringenModel::getKringenVoorVerticale($this);
	}

}

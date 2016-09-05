<?php

/**
 * Lichting.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Lichting extends AbstractGroep {

	const leden = 'LichtingLedenModel';

	/**
	 * Lidjaar
	 * @var int
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
	public static function __static() {
		parent::__static();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	public function getUrl() {
		return '/groepen/lichtingen/' . $this->lidjaar . '/';
	}

	/**
	 * Read-only: generated group
	 */
	public function mag($action) {
		return $action === A::Bekijken;
	}

	/**
	 * Read-only: generated group
	 */
	public static function magAlgemeen($action) {
		return $action === A::Bekijken;
	}

}

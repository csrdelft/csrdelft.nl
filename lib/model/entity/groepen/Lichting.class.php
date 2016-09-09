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

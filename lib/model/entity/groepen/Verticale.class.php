<?php

/**
 * Verticale.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Verticale extends AbstractGroep {

	const leden = 'VerticaleLedenModel';

	/**
	 * Primary key
	 * @var string
	 */
	public $letter;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'letter' => array(T::Char)
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

	/**
	 * Limit functionality: leden generated
	 */
	public function mag($action) {
		switch ($action) {

			case A::Bekijken:
			case A::Aanmaken:
			case A::Wijzigen:
				return parent::mag($action);
		}
		return false;
	}

	/**
	 * Limit functionality: leden generated
	 */
	public static function magAlgemeen($action) {
		switch ($action) {

			case A::Bekijken:
			case A::Aanmaken:
			case A::Wijzigen:
				return parent::magAlgemeen($action);
		}
		return false;
	}

}

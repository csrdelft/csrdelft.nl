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

	/**
	 * Has permission for action?
	 * 
	 * @param string $action
	 * @return boolean
	 */
	public function mag($action) {
		switch ($action) {

			// Uitzondering zodat beheerders niet overal een aanmeldknop krijgen
			case A::Aanmelden:
			case A::Bewerken:
			case A::Afmelden:
				break;

			default:
				// Maker van groep mag alles
				if ($this->maker_uid === LoginModel::getUid()) {
					return true;
				}
		}
		return static::magAlgemeen($action);
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 * 
	 * @param string $action
	 * @return boolean
	 */
	public static function magAlgemeen($action) {
		switch ($action) {

			case A::Bekijken:
				return LoginModel::mag('P_LEDEN_READ');

			// Uitzondering zodat beheerders niet overal een aanmeldknop krijgen
			case A::Aanmelden:
			case A::Bewerken:
			case A::Afmelden:
				break;

			default:
				// Beheerder mag alles
				if (LoginModel::mag('Bestuur:Vice-Abactis')) {
					return true;
				}
		}
		return false;
	}

}

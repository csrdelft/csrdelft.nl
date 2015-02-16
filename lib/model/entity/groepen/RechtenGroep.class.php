<?php

/**
 * RechtenGroep.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een groep beperkt voor rechten.
 */
class RechtenGroep extends AbstractGroep {

	const leden = 'RechtenGroepLedenModel';

	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 */
	public $rechten_aanmelden;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'rechten_aanmelden' => array(T::String)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groepen';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	public function getUrl() {
		return '/groepen/overig/' . $this->id . '/';
	}

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function mag($action) {
		switch ($action) {

			case A::Bekijken:
			case A::Aanmelden:
			case A::Bewerken:
			case A::Afmelden:
				if (!LoginModel::mag($this->rechten_aanmelden)) {
					return false;
				}
				break;
		}
		return parent::mag($action);
	}

}

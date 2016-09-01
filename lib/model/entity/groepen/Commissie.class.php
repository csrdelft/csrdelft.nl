<?php

require_once 'model/entity/groepen/CommissieSoort.enum.php';

/**
 * Commissie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een commissie is een groep waarvan de groepsleden een specifieke functie (kunnen) hebben.
 * 
 */
class Commissie extends AbstractGroep {

	const leden = 'CommissieLedenModel';

	/**
	 * (Bestuurs-)Commissie / SjaarCie
	 * @var CommissieSoort
	 */
	public $soort;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'soort' => array(T::Enumeration, false, 'CommissieSoort'),
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'commissies';

	/**
	 * Extend the persistent attributes.
	 */
	public static function __static() {
		parent::__static();
		self::$persistent_attributes = parent::$persistent_attributes + self::$persistent_attributes;
	}

	public function getUrl() {
		return '/groepen/commissies/' . $this->id . '/';
	}

	/**
	 * Rechten voor de gehele klasse of soort groep?
	 * 
	 * @param AccessAction $action
	 * @param string $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $soort = null) {
		switch ($soort) {

			case CommissieSoort::SjaarCie:
				if (LoginModel::mag('commissie:NovCie')) {
					return true;
				}
				break;
		}
		return parent::magAlgemeen($action);
	}

}

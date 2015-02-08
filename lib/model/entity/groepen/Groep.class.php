<?php

require_once 'model/entity/groepen/GroepStatus.enum.php';
require_once 'model/entity/groepen/GroepLid.class.php';

/**
 * Groep.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een groep met leden.
 */
class Groep extends PersistentEntity {

	const leden = 'GroepLedenModel';

	/**
	 * Primary key
	 * @var int
	 */
	public $id;
	/**
	 * Naam
	 * @var string
	 */
	public $naam;
	/**
	 * Naam voor opvolging
	 * @var string
	 */
	public $familie;
	/**
	 * Datum en tijd begin 
	 * @var string
	 */
	public $begin_moment;
	/**
	 * Datum en tijd einde
	 * @var string
	 */
	public $eind_moment;
	/**
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 */
	public $status;
	/**
	 * Korte omschrijving
	 * @var string
	 */
	public $samenvatting;
	/**
	 * Lange omschrijving
	 * @var string
	 */
	public $omschrijving;
	/**
	 * Serialized keuzelijst(en)
	 * @var string
	 */
	public $keuzelijst;
	/**
	 * Lidnummer van aanmaker
	 * @var string
	 */
	public $maker_uid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id'			 => array(T::Integer, false, 'auto_increment'),
		'naam'			 => array(T::String),
		'familie'		 => array(T::String),
		'begin_moment'	 => array(T::DateTime),
		'eind_moment'	 => array(T::DateTime, true),
		'status'		 => array(T::Enumeration, false, 'GroepStatus'),
		'samenvatting'	 => array(T::Text),
		'omschrijving'	 => array(T::Text, true),
		'keuzelijst'	 => array(T::String, true),
		'maker_uid'		 => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groepen';

	public function getUrl() {
		return '/groepen/overig/' . $this->id . '/';
	}

	/**
	 * Is lid van deze groep?
	 * 
	 * @param string $uid
	 * @return GroepLid
	 */
	public function getLid($uid) {
		$leden = static::leden;
		return $leden::get($this, $uid);
	}

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return GroepLid[]
	 */
	public function getLeden() {
		$leden = static::leden;
		return $leden::instance()->getLedenVoorGroep($this);
	}

	public function aantalLeden() {
		$leden = static::leden;
		return $leden::instance()->count('groep_id = ?', array($this->id));
	}

	public function getStatistieken() {
		$leden = static::leden;
		return $leden::instance()->getStatistieken($this);
	}

	public function getFamilieSuggesties() {
		return Database::sqlSelect(array('DISTINCT familie'), $this->getTableName())->fetchAll(PDO::FETCH_COLUMN);
	}

	public function getOpmerkingSuggesties() {
		if (isset($this->keuzelijst)) {
			$suggesties = array();
		} elseif ($this instanceof Commissie OR $this instanceof Bestuur) {
			$suggesties = CommissieFunctie::getTypeOptions();
		} else {
			$leden = static::leden;
			$suggesties = Database::sqlSelect(array('DISTINCT opmerking'), $leden::getTableName(), 'groep_id = ?', array($this->id))->fetchAll(PDO::FETCH_COLUMN);
		}
		return $suggesties;
	}

	/**
	 * Has permission for action?
	 * 
	 * @param string $action
	 * @return boolean
	 */
	public function mag($action) {
		if (!LoginModel::mag('P_LOGGED_IN')) {
			return false;
		}
		$leden = static::leden;
		$aangemeld = Database::sqlExists($leden::getTableName(), 'groep_id = ? AND uid = ?', array($this->id, LoginModel::getUid()));
		switch ($action) {

			case A::Aanmelden:
				if ($aangemeld) {
					return false;
				}
				break;

			case A::Bewerken:
				if (!$aangemeld) {
					return false;
				}
				break;

			case A::Afmelden:
				if (!$aangemeld) {
					return false;
				}
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

			case A::Aanmaken:
			case A::Aanmelden:
			case A::Bewerken:
			case A::Afmelden:
				if (!in_array(get_called_class(), array('Ketzer', 'Activiteit', 'Groep'))) {
					return false;
				}
			// fall through

			case A::Bekijken:
				if (LoginModel::mag('P_LEDEN_READ')) {
					return true;
				}
				break;

			default:
				// Moderators mogen alles
				if (LoginModel::mag('P_LEDEN_MOD,groep:P_GROEP:_MOD')) {
					return true;
				}
		}
		return false;
	}

}

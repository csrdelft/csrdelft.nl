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
	 * URL van website
	 * @var string
	 */
	public $website;
	/**
	 * Lidnummer van aanmaker
	 * @var string
	 */
	public $maker_uid;
	/**
	 * Rechten benodigd voor beheren
	 * @var string
	 */
	public $rechten_beheren;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id'				 => array(T::Integer, false, 'auto_increment'),
		'naam'				 => array(T::String),
		'familie'			 => array(T::String, true),
		'status'			 => array(T::Enumeration, false, 'GroepStatus'),
		'samenvatting'		 => array(T::Text),
		'omschrijving'		 => array(T::Text, true),
		'keuzelijst'		 => array(T::String, true),
		'begin_moment'		 => array(T::DateTime),
		'eind_moment'		 => array(T::DateTime, true),
		'website'			 => array(T::String, true),
		'maker_uid'			 => array(T::UID),
		'rechten_beheren'	 => array(T::String, true)
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
		$leden = $this->getLeden();
		return count($leden);
	}

	public function getStatistieken() {
		$leden = static::leden;
		return $leden::instance()->getStatistieken($this);
	}

	public function getOpvolgingSuggesties() {
		$suggesties = array();
		foreach (Database::sqlSelect(array('DISTINCT familie'), static::$table_name) as $suggestie) {
			$suggesties[] = $suggestie[0];
		}
		return $suggestie;
	}

	public function getOpmerkingSuggesties() {
		if (isset($this->keuzelijst)) {
			$suggesties = array();
		} elseif ($this instanceof Commissie OR $this instanceof Bestuur) {
			$suggesties = CommissieFunctie::getTypeOptions();
		} else {
			$suggesties = array();
			foreach ($this->getLeden() as $lid) {
				$suggesties[] = $lid->opmerking;
			}
		}
		return $suggesties;
	}

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @param string $uid affected Lid
	 * @return boolean
	 */
	public function mag($action, $uid = null) {
		if ($this->maker_uid === LoginModel::getUid()) {
			return true;
		}
		if (LoginModel::mag('P_LEDEN_MOD') OR LoginModel::mag($this->rechten_beheren)) {
			return true;
		}
		if ($action === A::Bekijken AND LoginModel::mag('P_LEDEN_READ')) {
			return true;
		}
		/**
		 * TODO
		 */
		return false;
		// rechten voor dit type groep?
		if (property_exists($this, 'soort') AND static::magAlgemeen($action, $this->soort)) {
			return true;
		} elseif (static::magAlgemeen($action)) {
			return true;
		}
		// rechten voor deze specifieke groep?
		if (LoginModel::mag(AccessModel::get(get_class($this), $action, $this->id))) {
			return true;
		}
		return false;
	}

	/**
	 * TODO
	 * 
	 * @param string $action
	 * @param string $soort
	 * @return boolean
	 */
	public static function magAlgemeen($action, $soort = null) {
		if (LoginModel::mag(AccessModel::get(get_called_class(), $action, '*'))) {
			return true;
		}
		if ($soort !== null AND property_exists($this, 'soort') AND LoginModel::mag(AccessModel::get(get_called_class(), $action, $soort))) {
			return true;
		}
		return false;
	}

}

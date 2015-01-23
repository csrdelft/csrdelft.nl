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
	public $door_uid;
	/**
	 * Verwijderd
	 * @var boolean
	 */
	public $verwijderd;
	/**
	 * Serialized keuzelijst(en)
	 * @var string
	 */
	public $keuzelijst;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id'			 => array(T::Integer, false, 'auto_increment'),
		'naam'			 => array(T::String),
		'samenvatting'	 => array(T::Text),
		'omschrijving'	 => array(T::Text, true),
		'begin_moment'	 => array(T::DateTime),
		'eind_moment'	 => array(T::DateTime, true),
		'website'		 => array(T::String, true),
		'door_uid'		 => array(T::UID),
		'verwijderd'	 => array(T::Boolean),
		'keuzelijst'	 => array(T::String, true)
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

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return GroepLid[]
	 */
	public function getLeden($status = null) {
		$class = static::leden;
		return $class::instance()->getLedenVoorGroep($this, $status);
	}

	public function getStatistieken() {
		$class = static::leden;
		return $class::instance()->getStatistieken($this);
	}

	public function getSuggestions() {
		if ($this instanceof Commissie OR $this instanceof Bestuur) {
			$suggestions = CommissieFunctie::getTypeOptions();
		} else {
			$suggestions = array();
			foreach ($this->getLeden() as $lid) {
				$suggestions[] = $lid->opmerking;
			}
		}
		return $suggestions;
	}

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function mag($action) {
		if (LoginModel::getUid() === $this->door_uid OR LoginModel::mag('P_LEDEN_MOD')) {
			return true;
		}
		$algemeen = AccessModel::get(get_class($this), $action, '*');
		if ($algemeen AND LoginModel::mag($algemeen)) {
			return true;
		}
		if (property_exists($this, 'soort')) {
			$soort = AccessModel::get(get_class($this), $action, $this->soort);
			if ($soort AND LoginModel::mag($soort)) {
				return true;
			}
		}
		$specifiek = AccessModel::get(get_class($this), $action, $this->id);
		if ($specifiek AND LoginModel::mag($specifiek)) {
			return true;
		}
		return false;
	}

}

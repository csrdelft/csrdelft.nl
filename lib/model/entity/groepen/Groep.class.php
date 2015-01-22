<?php

require_once 'model/entity/groepen/GroepStatus.enum.php';
require_once 'model/entity/groepen/GroepLid.class.php';

/**
 * Groep.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een groep met leden en status.
 * Optioneel in familie voor opvolging.
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
		'door_uid'		 => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('id');

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

	/**
	 * Has permission for action?
	 * 
	 * @param AccessAction $action
	 * @return boolean
	 */
	public function mag($action) {
		$algemeen = AccessModel::get(get_class($this), $action, null);
		if (LoginModel::mag($algemeen)) {
			return true;
		}
		$specifiek = AccessModel::get(get_class($this), $action, $this->id);
		if (LoginModel::mag($specifiek)) {
			return true;
		}
		return false;
	}

}

<?php

require_once 'model/entity/groepen/GroepLid.class.php';
require_once 'model/entity/groepen/GroepStatus.enum.php';

/**
 * Groep.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een groep met leden en status.
 * Optioneel in familie voor opvolging.
 */
class Groep extends PersistentEntity {

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
	 * Groepsleden
	 * @var GroepLid[]
	 */
	private $groep_leden;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id'			 => array(T::Integer, false, 'auto_increment'),
		'naam'			 => array(T::String),
		'samenvatting'	 => array(T::Text),
		'omschrijving'	 => array(T::Text),
		'begin_moment'	 => array(T::DateTime, true),
		'eind_moment'	 => array(T::DateTime, true),
		'website'		 => array(T::String),
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
	public function getGroepLeden() {
		if (!isset($this->groep_leden)) {
			$this->setGroepLeden(GroepLedenModel::instance()->getLedenVoorGroep($this));
		}
		return $this->groep_leden;
	}

	public function hasGroepLeden() {
		$this->getGroepLeden();
		return !empty($this->groep_leden);
	}

	private function setGroepLeden(array $leden) {
		$this->groep_leden = $leden;
	}

	/**
	 * Gaat er vanuit dat er precies 1 groeplid met de gevraagde functie bestaat in deze groep.
	 * 
	 * @see GroepFunctie
	 * @param string $functie
	 * @return GroepLid
	 */
	public function getGroepLidByFunctie($functie) {
		foreach ($this->getGroepLeden() as $groeplid) {
			if ($groeplid->omschrijving === $functie) {
				return $groeplid;
			}
		}
	}

	public function getStatistieken() {
		return GroepLedenModel::instance()->getStatistieken($this);
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

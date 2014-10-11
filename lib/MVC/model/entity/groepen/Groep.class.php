<?php

require_once 'MVC/model/entity/groepen/GroepLid.class.php';
require_once 'MVC/model/entity/groepen/GroepStatus.enum.php';

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
	 * Deze groep valt onder deze categorie
	 * @var int
	 */
	public $categorie_id;
	/**
	 * Familie (opvolging)
	 * @var string
	 */
	public $familie_id;
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
	 * o.t. / h.t. / f.t.
	 * @var GroepStatus
	 */
	public $status;
	/**
	 * Rechten benodigd voor bekijken
	 * @var string
	 */
	public $rechten_bekijken;
	/**
	 * Rechten benodigd voor aanmelden
	 * @var string
	 */
	public $rechten_aanmelden;
	/**
	 * Rechten benodigd voor beheren
	 * @var string
	 */
	public $rechten_beheren;
	/**
	 * URL van website
	 * @var string
	 */
	public $website;
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
		'id'				 => array(T::Integer, false, 'auto_increment'),
		'categorie_id'		 => array(T::Integer),
		'familie_id'		 => array(T::String),
		'naam'				 => array(T::String),
		'samenvatting'		 => array(T::Text),
		'omschrijving'		 => array(T::Text),
		'begin_moment'		 => array(T::DateTime, true),
		'eind_moment'		 => array(T::DateTime, true),
		'status'			 => array(T::Enumeration, false, 'GroepStatus'),
		'rechten_bekijken'	 => array(T::String),
		'rechten_aanmelden'	 => array(T::String),
		'rechten_beheren'	 => array(T::String),
		'website'			 => array(T::String)
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
		return count($this->getGroepLeden()) > 0;
	}

	private function setGroepLeden(array $leden) {
		$this->groep_leden = $leden;
	}

	/**
	 * Gaat er vanuit dat er precies 1 groeplid met de gevraagde functie bestaat in deze groep.
	 * 
	 * @see BestuurFunctie
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

	public function magBekijken() {
		return LoginModel::mag($this->rechten_bekijken);
	}

	public function magAanmelden() {
		return LoginModel::mag($this->rechten_aanmelden);
	}

	public function magBeheren() {
		return LoginModel::mag($this->rechten_beheren);
	}

}

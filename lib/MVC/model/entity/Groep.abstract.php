<?php

require_once 'MVC/model/entity/GroepLid.class.php';
require_once 'MVC/model/entity/GroepStatus.enum.php';

/**
 * Groep.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een groep met leden en status.
 * 
 */
abstract class Groep extends PersistentEntity {

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
	 * Naam
	 * @var string
	 */
	public $naam;
	/**
	 * Familie van generaties
	 * @var string
	 */
	public $familie_id;
	/**
	 * Uid van aanmaker
	 * @var string
	 */
	public $eigenaar_lid_id;
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
	 * @see GroepStatus
	 * @var string
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
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'categorie_id' => 'int(11) NOT NULL',
		'naam' => 'varchar(255) NOT NULL',
		'familie_id' => 'varchar(255) NOT NULL',
		'eigenaar_lid_id' => 'varchar(4) NOT NULL',
		'samenvatting' => 'text NOT NULL',
		'omschrijving' => 'text NOT NULL',
		'begin_moment' => 'datetime DEFAULT NULL',
		'eind_moment' => 'datetime DEFAULT NULL',
		'status' => 'varchar(4) NOT NULL',
		'rechten_bekijken' => 'varchar(255) NOT NULL',
		'rechten_aanmelden' => 'varchar(255) NOT NULL',
		'rechten_beheren' => 'varchar(255) NOT NULL',
		'website' => 'varchar(255) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('groep_id');

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

	public function setGroepLeden(array $leden) {
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

	public function magAanmelden() {
		return LoginLid::mag($this->rechten_aanmelden);
	}

	public function magBeheren() {
		return LoginLid::mag($this->rechten_beheren);
	}

}

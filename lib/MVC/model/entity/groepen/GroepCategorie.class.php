<?php

/**
 * GroepCategorie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een categorie van groepen.
 * 
 */
class GroepCategorie extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $categorie_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Groepen weergeven in overzicht
	 * @var boolean
	 */
	public $toon_overzicht;
	/**
	 * Groepen weergeven in profiel
	 * @var boolean
	 */
	public $toon_profiel;
	/**
	 * Groepen synchroniseren met LDAP
	 * @var string
	 */
	public $sync_ldap;
	/**
	 * Rechten benodigd voor aanmaken nieuwe groepen
	 * @var string
	 */
	public $rechten_aanmaken;
	/**
	 * Rechten benodigd voor beheren bestaande groepen
	 * @var string
	 */
	public $rechten_beheren;
	/**
	 * Standaard rechten benodigd voor bekijken groep
	 * @var string
	 */
	public $rechten_bekijken;
	/**
	 * Standaard rechten benodigd voor aanmelden groep
	 * @var string
	 */
	public $rechten_aanmelden;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'categorie_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'titel' => 'varchar(255) NOT NULL',
		'toon_overzicht' => 'boolean NOT NULL',
		'toon_profiel' => 'boolean NOT NULL',
		'sync_ldap' => 'boolean NOT NULL',
		'rechten_aanmaken' => 'varchar(255) NOT NULL',
		'rechten_beheren' => 'varchar(255) NOT NULL',
		'rechten_bekijken' => 'varchar(255) NOT NULL',
		'rechten_aanmelden' => 'varchar(255) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('categorie_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groep_categorien';

	public function magBeheren() {
		return LoginLid::mag($this->rechten_beheren);
	}

}

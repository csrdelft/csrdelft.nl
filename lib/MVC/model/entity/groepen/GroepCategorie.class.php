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
	protected static $persistent_attributes = array(
		'categorie_id' => array(T::Integer, false, 'auto_increment'),
		'titel' => array(T::String),
		'toon_overzicht' => array(T::Boolean),
		'toon_profiel' => array(T::Boolean),
		'sync_ldap' => array(T::Boolean),
		'rechten_aanmaken' => array(T::String),
		'rechten_beheren' => array(T::String),
		'rechten_bekijken' => array(T::String),
		'rechten_aanmelden' => array(T::String)
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
		return LoginModel::mag($this->rechten_beheren);
	}

}

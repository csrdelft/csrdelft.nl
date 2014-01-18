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
	 * Naam
	 * @var string
	 */
	public $naam;
	/**
	 * Omschrijving
	 * @var string
	 */
	public $omschrijving;
	/**
	 * Zichtbaar in overzicht
	 * @var boolean
	 */
	public $zichtbaar;
	/**
	 * Weergave volgorde
	 * @var int
	 */
	public $prioriteit;
	/**
	 * Historie weergeven in overzicht
	 * @var string
	 */
	public $toon_historie;
	/**
	 * Groepen weergeven in profiel
	 * @var string
	 */
	public $toon_profiel;
	/**
	 * Groepen synchroniseren met LDAP
	 * @var string
	 */
	public $sync_ldap;
	/**
	 * Rechten benodigd voor aanmaken
	 * @var string
	 */
	public $schrijfrechten = 'P_LEDEN_MOD';
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'categorie_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'naam' => 'varchar(255) NOT NULL',
		'omschrijving' => 'text NOT NULL',
		'zichtbaar' => 'tinyint(1) NOT NULL',
		'prioriteit' => 'int(11) NOT NULL',
		'toon_historie' => 'tinyint(1) NOT NULL',
		'toon_profiel' => 'tinyint(1) NOT NULL',
		'sync_ldap' => 'tinyint(1) NOT NULL',
		'schrijfrechten' => 'varchar(25) NOT NULL'
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

}

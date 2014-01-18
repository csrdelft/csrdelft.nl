<?php

/**
 * Groep.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een groep met leden.
 * 
 */
abstract class Groep extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $groep_id;
	/**
	 * Type van groep
	 * @var string
	 */
	public static $class_name = 'Groep';
	/**
	 * Deze groep valt onder deze categorie
	 * @var int
	 */
	public $categorie_id;
	/**
	 * Familie van generaties
	 * @var string
	 */
	public $familie_id;
	/**
	 * Uid van aanmaker
	 * @var string
	 */
	public $lid_id;
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
	public $moment_begin;
	/**
	 * Datum en tijd einde
	 * @var string
	 */
	public $moment_einde;
	/**
	 * Rechten benodigd voor wijzigen
	 * @var string
	 */
	public $schrijfrechten = 'P_LEDEN_MOD';
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'groep_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'class_name' => 'varchar(255) NOT NULL',
		'categorie_id' => 'int(11) NOT NULL',
		'familie_id' => 'varchar(255) NOT NULL',
		'lid_id' => 'varchar(4) NOT NULL',
		'samenvatting' => 'text NOT NULL',
		'omschrijving' => 'text NOT NULL',
		'moment_begin' => 'datetime DEFAULT NULL',
		'moment_einde' => 'datetime DEFAULT NULL',
		'schrijfrechten' => 'varchar(25) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('groep_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groepen';

}

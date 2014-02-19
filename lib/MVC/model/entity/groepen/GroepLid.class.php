<?php

/**
 * GroepLid.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een lid van een groep.
 * 
 */
class GroepLid extends PersistentEntity {

	/**
	 * Lid van deze groep
	 * @var int
	 */
	public $groep_id;
	/**
	 * Uid van lid
	 * @var string
	 */
	public $lid_id;
	/**
	 * Omschrijving bij lidmaatschap
	 * @var string
	 */
	public $omschrijving;
	/**
	 * Datum en tijd van toevoegen
	 * @var string
	 */
	public $datum_tijd;
	/**
	 * Volgorde van weergave
	 * @var string
	 */
	public $prioriteit;
	/**
	 * Uid van aanmelder
	 * @var string
	 */
	public $door_lid_id;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'groep_id' => 'int(11) NOT NULL AUTO_INCREMENT',
		'lid_id' => 'varchar(4) NOT NULL',
		'omschrijving' => 'text NOT NULL',
		'datum_tijd' => 'datetime NOT NULL',
		'prioriteit' => 'int(11) NOT NULL',
		'door_lid_id' => 'varchar(4) NOT NULL'
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('groep_id', 'lid_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'groep_leden';

}

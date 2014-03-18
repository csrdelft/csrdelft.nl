<?php

/**
 * CorveeKwalificatie.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Een CorveeKwalificatie instantie geeft aan dat een lid gekwalificeerd is voor een functie en sinds wanneer.
 * Dit is benodigd voor sommige CorveeFuncties zoals kwalikok.
 * 
 * 
 * Zie ook CorveeFunctie.class.php
 * 
 */
class CorveeKwalificatie extends PersistentEntity {

	/**
	 * Lid id
	 * @var string
	 */
	public $lid_id;
	/**
	 * Functie id
	 * @var int
	 */
	public $functie_id;
	/**
	 * Datum + tijd
	 * @var string
	 */
	public $wanneer_toegewezen; #datetime
	/**
	 * Functie instantie
	 * @var CorveeFunctie
	 */
	public $corvee_functie;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'lid_id' => 'varchar(4) NOT NULL',
		'functie_id' => 'int(11) NOT NULL',
		'wanneer_toegewezen' => 'datetime NOT NULL'
	);
	/**
	 * Form input fields
	 * @var array
	 */
	protected static $input_fields = array(
		'lid_id' => array('LidField', 'Naam of lidnummer', 'leden')
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('lid_id', 'functie_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'crv_kwalificaties';

}

<?php

/**
 * KetzerKeuze.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een soort ketzeroptie heeft keuzemogelijkheden.
 * 
 */
class KetzerKeuze extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $optie_id;
	/**
	 * Primary key
	 * @var int
	 */
	public $lid_id;
	/**
	 * Gekozen waarde
	 * @var array
	 */
	public $keuze;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'optie_id' => 'int(11) DEFAULT NULL',
		'lid_id' => 'varchar(4) DEFAULT NULL',
		'keuze' => 'text NOT NULL'
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzerkeuzes';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('optie_id', 'lid_id');

}

<?php

/**
 * LedenMemoryScore.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class LedenMemoryScore extends PersistentEntity {

	/**
	 * Seconden
	 * @var int
	 */
	public $tijd;
	/**
	 * Aantal beurten
	 * @var int
	 */
	public $beurten;
	/**
	 * Aantal goed
	 * @var int
	 */
	public $goed;
	/**
	 * UUID
	 * @var string
	 */
	public $groep;
	/**
	 * Eerlijk verkregen score
	 * @var boolean
	 */
	public $eerlijk;
	/**
	 * Door lidnummer
	 * Foreign key
	 * @var string
	 */
	public $door_uid;
	/**
	 * Behaald op datum en tijd
	 * @var string
	 */
	public $wanneer;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'tijd'		 => array(T::Integer),
		'beurten'	 => array(T::Integer),
		'goed'		 => array(T::Integer),
		'groep'		 => array(T::Text),
		'eerlijk'	 => array(T::Boolean),
		'door_uid'	 => array(T::UID),
		'wanneer'	 => array(T::DateTime)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'memory_scores';

}

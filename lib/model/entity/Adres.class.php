<?php

/**
 * Adres.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Adres extends PersistentEntity {

	public $adres_id;
	public $naam;
	public $straat;
	public $plaats;
	public $huisnummer;
	public $toevoeging;
	public $postcode;
	public $land;
	public $telefoon;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'adres_id'	 => array(T::Integer, false, 'auto_increment'),
		'naam'		 => array(T::String),
		'straat'	 => array(T::String),
		'plaats'	 => array(T::String),
		'huisnummer' => array(T::Integer),
		'toevoeging' => array(T::String),
		'postcode'	 => array(T::String),
		'land'		 => array(T::String),
		'telefoon'	 => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('adres_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'adressen';

}

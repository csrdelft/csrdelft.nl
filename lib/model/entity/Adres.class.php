<?php

/**
 * Adres.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Adres extends PersistentEntity {

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
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'adressen';

}

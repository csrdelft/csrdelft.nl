<?php

/**
 * Factuur.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Factuur extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $factuur_id;
	/**
	 * Klant ID
	 * Foreign key
	 * @var int
	 */
	public $klant_id;
	/**
	 * Titel
	 * @var string
	 */
	public $titel;
	/**
	 * Toelichting
	 * @var string
	 */
	public $toelichting;
	/**
	 * IBAN rekening ontvanger
	 * @var string
	 */
	public $ontvangst_iban;
	/**
	 * Aantal termijnen
	 * @var int
	 */
	public $termijnen;
	/**
	 * DateTime doodlijn
	 * @var string
	 */
	public $doodlijn_moment;
	/**
	 * DateTime voldaan
	 * @var string
	 */
	public $voldaan_moment;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'factuur_id'		 => array(T::Integer, false, 'auto_increment'),
		'klant_id'			 => array(T::Integer),
		'titel'				 => array(T::String),
		'toelichting'		 => array(T::Text, true),
		'ontvangst_iban'	 => array(T::String, true),
		'termijnen'			 => array(T::Integer),
		'doodlijn_moment'	 => array(T::DateTime, true),
		'voldaan_moment'	 => array(T::DateTime, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('factuur_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'facturen';

}

<?php

/**
 * Leverancier.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Leverancier extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $leverancier_id;
	/**
	 * Naam
	 * @var string
	 */
	public $naam;
	/**
	 * Contactgegevens
	 * @var string
	 */
	public $contact;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'leverancier_id'	 => array(T::Integer, false, 'auto_increment'),
		'naam'			 => array(T::String),
		'contact'		 => array(T::Text, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('leverancier_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'leveranciers';

}

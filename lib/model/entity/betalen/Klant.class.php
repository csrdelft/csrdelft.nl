<?php

/**
 * Klant.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Klant extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $klant_id;
	/**
	 * Lidnummer
	 * Foreign key
	 * @var int
	 */
	public $uid;
	/**
	 * Saldo in centen
	 * @var int
	 */
	public $saldo;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'klant_id'	 => array(T::Integer, false, 'auto_increment'),
		'uid'		 => array(T::UID, true),
		'saldo'		 => array(T::Integer)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('klant_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'klanten';

}

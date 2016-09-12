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
	 * CiviSaldo in centen
	 * @var int
	 */
	public $civi_saldo;
	/**
	 * SoccieSaldo in centen
	 * @var int
	 */
	public $soccie_saldo;
	/**
	 * MaalcieSaldo in centen
	 * @var int
	 */
	public $maalcie_saldo;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'klant_id'		 => array(T::Integer, false, 'auto_increment'),
		'uid'			 => array(T::UID, true),
		'civi_saldo'	 => array(T::Integer, true),
		'soccie_saldo'	 => array(T::Integer, true),
		'maalcie_saldo'	 => array(T::Integer, true)
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

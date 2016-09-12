<?php

/**
 * Transactie.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class Transactie extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $transactie_id;
	/**
	 * Factuur ID
	 * Foreign key
	 * @var int
	 */
	public $factuur_id;
	/**
	 * BetalingsMethode
	 * @var string
	 */
	public $betalingsmethode;
	/**
	 * Bedrag in centen
	 * @var string
	 */
	public $bedrag;
	/**
	 * IBAN rekening ontvanger
	 * @var string
	 */
	public $ontvangst_iban;
	/**
	 * IBAN rekening betaler
	 * @var int
	 */
	public $betaler_iban;
	/**
	 * Geslaagd
	 * @var boolean
	 */
	public $geslaagd;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'transactie_id'		 => array(T::Integer, false, 'auto_increment'),
		'factuur_id'		 => array(T::Integer),
		'betalingsmethode'	 => array(T::Enumeration, false, 'BetalingsMethode'),
		'bedrag'			 => array(T::Integer),
		'ontvangst_iban'	 => array(T::String, true),
		'betaler_iban'		 => array(T::String, true),
		'geslaagd'			 => array(T::Boolean, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('transactie');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'transacties';

}

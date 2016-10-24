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
	 * DateTime moment
	 * @var string
	 */
	public $moment;
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
	 * Link transactie ID
	 * Foreign key
	 * @var int
	 */
	public $link_transactie_id;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'transactie_id'		 => array(T::Integer, false, 'auto_increment'),
		'factuur_id'		 => array(T::Integer),
		'moment'			 => array(T::DateTime),
		'betalingsmethode'	 => array(T::Enumeration, false, 'BetalingsMethode'),
		'bedrag'			 => array(T::Integer),
		'ontvangst_iban'	 => array(T::String, true),
		'betaler_iban'		 => array(T::String, true),
		'geslaagd'			 => array(T::Boolean, true),
		'link_transactie_id' => array(T::Integer, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('transactie_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'transacties';

}

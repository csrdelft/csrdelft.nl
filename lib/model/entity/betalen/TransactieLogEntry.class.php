<?php

/**
 * TransactieLog.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class TransactieLogEntry extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $id;
	/**
	 * Transactie ID
	 * Foreign key
	 * @var int
	 */
	public $transactie_id;
	/**
	 * Serialized transactie
	 * @var string
	 */
	public $transactie_serialized;
	/**
	 * Serialized factuur
	 * @var string
	 */
	public $factuur_serialized;
	/**
	 * Blockchain previous hash
	 * @var string
	 */
	public $blockchain_previous_hash;
	/**
	 * Blockchain hash
	 * @var string
	 */
	public $blockchain_hash;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'id'						 => array(T::Integer, false, 'auto_increment'),
		'transactie_id'				 => array(T::Integer),
		'transactie_serialized'		 => array(T::Text),
		'factuuur_serialized'		 => array(T::Text),
		'blockchain_previous_hash'	 => array(T::String),
		'blockchain_hash'			 => array(T::String)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'transactie_log';

}

<?php

/**
 * KetzerKeuze.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een gekozen keuzemogelijkheid door een groeplid.
 * 
 */
class KetzerKeuze extends PersistentEntity {

	/**
	 * Primary key
	 * @var array
	 */
	public $optie_id;
	/**
	 * Primary key
	 * @var int
	 */
	public $uid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'optie_id'	 => array(T::Integer),
		'uid'		 => array(T::UID)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'ketzer_keuzes';
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('optie_id', 'uid');

}

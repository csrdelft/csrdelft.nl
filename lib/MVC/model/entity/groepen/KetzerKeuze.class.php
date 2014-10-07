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
	 * Keuze in deze ketzer
	 * @var int
	 */
	public $ketzer_id;
	/**
	 * Keuze van deze KetzerSelector
	 * @var int
	 */
	public $select_id;
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
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'ketzer_id' => array(T::Integer),
		'select_id' => array(T::Integer),
		'optie_id' => array(T::Integer),
		'uid' => array(T::UID)
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
	protected static $primary_key = array('ketzer_id', 'select_id', 'optie_id', 'uid');

}

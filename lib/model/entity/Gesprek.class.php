<?php

/**
 * Gesprek.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Gesprek extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $gesprek_id;
	/**
	 * DateTime last message
	 * @var string
	 */
	public $laatste_update;
	/**
	 * Laatste bericht door
	 * @var string
	 */
	public $laatste_uid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'gesprek_id'	 => array(T::Integer, false, 'auto_increment'),
		'laatste_update' => array(T::DateTime),
		'laatste_uid'	 => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('gesprek_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'gesprekken';

}

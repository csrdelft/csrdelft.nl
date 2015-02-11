<?php

/**
 * GesprekGelezen.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GesprekGelezen extends PersistentEntity {

	/**
	 * Primary key
	 * @var int
	 */
	public $gesprek_id;
	/**
	 * DateTime last message
	 * @var string
	 */
	public $moment;
	/**
	 * Laatste bericht door
	 * @var string
	 */
	public $door_uid;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'bericht_id' => array(T::Integer, false, 'auto_increment'),
		'moment'	 => array(T::DateTime),
		'auteur_uid' => array(T::UID)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('bericht_id');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'gesprek_berichten';

}

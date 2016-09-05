<?php

/**
 * GesprekDeelnemer.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class GesprekDeelnemer extends PersistentEntity {

	/**
	 * Shared primary key
	 * @var int
	 */
	public $gesprek_id;
	/**
	 * Shared primary key
	 * @var string
	 */
	public $uid;
	/**
	 * DateTime
	 * @var string
	 */
	public $toegevoegd_moment;
	/**
	 * DateTime
	 * @var string
	 */
	public $gelezen_moment;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'gesprek_id'		 => array(T::UnsignedInteger, false, 'auto_increment'),
		'uid'				 => array(T::UID),
		'toegevoegd_moment'	 => array(T::DateTime),
		'gelezen_moment'	 => array(T::DateTime)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('gesprek_id', 'uid');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'gesprek_deelnemers';

}

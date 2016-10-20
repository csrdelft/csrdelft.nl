<?php

/**
 * RemovedAttribute.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class RemovedAttribute extends PersistentEntity {

	/**
	 * Shared primary key
	 * Foreign key
	 * @var int
	 */
	public $object_id;
	/**
	 * Shared primary key
	 * @var string
	 */
	public $name;
	/**
	 * Original value of removed attribute
	 * @var string
	 */
	public $value;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'object_id'	 => array(T::Integer),
		'name'		 => array(T::String),
		'value'		 => array(T::LongText, true)
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('object_id', 'name');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'removed_attributes';

}

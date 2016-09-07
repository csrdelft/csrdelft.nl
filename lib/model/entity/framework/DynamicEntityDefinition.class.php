<?php

/**
 * DynamicEntityDefinition.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * The database table defines the dynamic entity.
 * 
 */
class DynamicEntityDefinition {

	/**
	 * Database table columns
	 * @var array
	 */
	public $persistent_attributes;
	/**
	 * Database primary key
	 * @var array
	 */
	public $primary_key;
	/**
	 * Database foreign keys
	 */
	public $foreign_keys;
	/**
	 * Database indexes
	 * @var array
	 */
	public $indexes;
	/**
	 * Database table name
	 * @var string
	 */
	public $table_name;

}

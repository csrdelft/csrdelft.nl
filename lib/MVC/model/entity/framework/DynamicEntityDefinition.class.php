<?php

/**
 * DynamicEntityDefinition.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Type of dynamic entity to keep track and inherit superclass attributes.
 * 
 */
class DynamicEntityDefinition extends PersistentEntity {

	/**
	 * Primary key
	 * @var string
	 */
	public $entity_name;
	/**
	 * Superclass (can be any subclass of PersistentEntity)
	 * @var string
	 */
	public $parent_entity;
	/**
	 * Table that stores the persistent entity objects
	 * @var string
	 */
	public $table;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'entity_name'	 => array(T::String),
		'parent_entity'	 => array(T::String),
		'table'			 => array(T::String),
	);
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array('entity_name');
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'dynamic_entities';

}

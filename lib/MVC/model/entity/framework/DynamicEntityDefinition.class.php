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
	 * Non-static attribute of PersistentEntity
	 * @var array
	 */
	public $persistent_attributes;
	/**
	 * Non-static attribute of PersistentEntity
	 * @var array
	 */
	public $primary_key;
	/**
	 * Non-static attribute of PersistentEntity
	 * @var string
	 */
	public $table_name;

}

<?php

require_once 'MVC/model/entity/framework/PersistentEntity.abstract.php';

/**
 * DynamicEntity.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Dynamic entities are defined by the table structure instead of the other way around. 
 * Conversion: only define the new class and dynamicly load the old class definition.
 * 
 */
class DynamicEntity extends PersistentEntity {

	/**
	 * The definition of this entity
	 * @var DynamicEntityDefinition
	 */
	public static $definition;

	/**
	 * Static constructor is called (by inheritance) first and only from PersistenceModel.
	 */
	public static function __constructStatic() {
		$orm = self::$definition->parent_entity;
		$orm::__constructStatic();
		self::$table_name = self::$definition->table;
		foreach (DatabaseAdmin::instance()->sqlDescribeTable(self::getTableName()) as $attribute) {
			self::$persistent_attributes[] = PersistentAttribute::makeDefinition($attribute);
			if ($attribute->key === 'PRI') {
				self::$primary_key[] = $attribute->field;
			}
		}
	}

	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array();
	/**
	 * Database primary key
	 * @var array
	 */
	protected static $primary_key = array();
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name;

	public function __set($attribute, $value) {
		$this->$attribute = $value;
	}

	public function __get($attribute) {
		if (property_exists(get_called_class(), $attribute)) {
			return $this->$attribute;
		}
		return null;
	}

	public function __isset($attribute) {
		return $this->__get($attribute) !== null;
	}

	public function __unset($attribute) {
		if ($this->__isset($attribute)) {
			unset($this->$attribute);
		}
	}

}

<?php

/**
 * DynamicEntity.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Dynamic entities are defined by the table structure instead of the other way around. 
 * 
 */
class DynamicEntity extends PersistentEntity {

	/**
	 * Static constructor is called (by inheritance) first and only from PersistenceModel.
	 */
	public static function __constructStatic() {
		foreach (DatabaseAdmin::instance()->sqlDescribeTable(static::getTableName()) as $attribute) {
			static::$persistent_attributes[] = $this->translatePersistentAttribute($attribute);
			if ($attribute->key === 'PRI') {
				static::$primary_key[] = $attribute->field;
			}
		}
		static::$table_name = ''; // TODO weet je pas on runtime
		// TODO: inheritance
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

	public function __set($name, $value) {
		$this->$name = $value;
	}

	public function __get($name) {
		if (property_exists(get_called_class(), $name)) {
			return $this->$name;
		}
		return null;
	}

	public function __isset($name) {
		return $this->__get($name) !== null;
	}

	public function __unset($name) {
		if ($this->__isset($name)) {
			unset($this->$name);
		}
	}

	/**
	 * To compare table description of MySQL.
	 * 
	 * @param PersistentAttribute $attribute
	 * @reaturn array $definition
	 */
	private static function translatePersistentAttribute(PersistentAttribute $attribute) {
		$definition = array();
		if ($attribute->type === 'tinyint(1)') {
			$definition[] = T::Boolean;
		} elseif ($attribute->type === 'int(11)') {
			$definition[] = T::Integer;
		} elseif ($attribute->type === 'varchar(255)') {
			$definition[] = T::String;
		} elseif ($attribute->type === 'varchar(4)') {
			$definition[] = T::UID;
		} elseif ($attribute->type === 'char(1)') {
			$definition[] = T::Char;
		} else {
			$definition[] = T::Enumeration;
		}
		if ($attribute->null === 'YES') {
			$definition[] = true;
		} else {
			$definition[] = false;
		}
		$definition[] = $attribute->extra;
		return $definition;
	}

}

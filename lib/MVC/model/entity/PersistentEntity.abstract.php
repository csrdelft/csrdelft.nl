<?php

require_once 'MVC/model/entity/PersistentEnum.interface.php';
require_once 'MVC/model/entity/PersistentType.enum.php';
require_once 'MVC/model/entity/PersistentField.class.php';

/**
 * PersistentEntity.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Requires static properties in superclass: $table_name, $persistent_fields, $primary_key
 * Optional: static $rename_fields = array('oldname' => 'newname');
 */
abstract class PersistentEntity implements JsonSerializable {

	/**
	 * Constructor is called late (after fields are set)
	 * by PDO::FETCH_CLASS with $cast = true.
	 * 
	 * @param boolean $cast Regular construction should not cast unset properties!
	 */
	public function __construct($cast = false) {
		if ($cast) {
			$this->castValues();
		}
	}

	/**
	 * Static constructor is called (by inheritance) first and only from PersistenceModel.
	 */
	public static function __constructStatic() {
		/*
		 * Override this to extend the persistent fields:
		 * 
		  parent::__constructStatic();
		  self::$persistent_fields = parent::$persistent_fields + self::$persistent_fields;
		 * 
		 * Optionally run conversion code before checkTables()
		 */
	}

	public static function getTableName() {
		return static::$table_name;
	}

	public static function getFields() {
		return array_keys(static::$persistent_fields);
	}

	public static function getFieldDefinition($field_name) {
		return static::$persistent_fields[$field_name];
	}

	public static function getPrimaryKey() {
		return static::$primary_key;
	}

	public function getUUID() {
		return implode('.', $this->getValues(true)) . '@' . get_class($this) . '.csrdelft.nl';
	}

	public function jsonSerialize() {
		$array = (array) $this;
		// strip non-public field prefixes
		foreach ($array as $key => $value) {
			$pos = strrpos($key, 0);
			if ($pos !== false) {
				$array[substr($key, $pos + 1)] = $value;
				unset($array[$key]);
			}
		}
		return $array;
	}

	/**
	 * Get the fields and their values of this object.
	 * 
	 * @param boolean $primary_key_only
	 * @return array
	 */
	public function getValues($primary_key_only = false) {
		$values = array();
		if ($primary_key_only) {
			$fields = $this->getPrimaryKey();
		} else {
			$fields = $this->getFields();
		}
		foreach ($fields as $field) {
			$values[$field] = $this->$field;
			//FIXME: werkomheen PDO/MySQL bug boolean/smallint
			if (is_bool($values[$field])) {
				$values[$field] = (int) $values[$field];
			}
		}
		if ($primary_key_only) {
			return array_values($values);
		}
		return $values;
	}

	/**
	 * Cast values to defined type.
	 * PDO does not do this automatically (yet).
	 */
	private function castValues() {
		foreach (static::$persistent_fields as $field => $definition) {
			if ($definition[0] === T::Boolean) {
				$this->$field = (boolean) $this->$field;
			} elseif ($definition[0] === T::Integer) {
				$this->$field = (int) $this->$field;
			} elseif ($definition[0] === T::Float) {
				$this->$field = (float) $this->$field;
			} elseif (DB_CHECK AND $definition[0] === T::Enumeration AND ! in_array($this->$field, $definition[2]::getTypeOptions())
			) {
				debugprint(static::getTableName() . '.' . $field . ' invalid ' . $definition[2] . ' value: ' . $this->$field);
			}
		}
	}

	/**
	 * To compare table description of MySQL.
	 * 
	 * @param string $name
	 * @param array $definition
	 * @return PersistentField
	 */
	private static function makePersistentField($name, array $definition) {
		$field = new PersistentField();
		$field->field = $name;
		$field->type = $definition[0];
		$field->default = null;
		if (isset($definition[1]) AND $definition[1]) {
			$field->null = 'YES';
		} else {
			$field->null = 'NO';
		}
		$field->extra = (isset($definition[2]) ? $definition[2] : '');
		if ($field->type === T::Boolean) {
			$field->type = 'tinyint(1)';
		} elseif ($field->type === T::Integer) {
			$field->type = 'int(11)';
		} elseif ($field->type === T::String) {
			$field->type = 'varchar(255)';
		} elseif ($field->type === T::UID) {
			$field->type = 'varchar(4)';
		} elseif ($field->type === T::Char) {
			$field->type = 'char(1)';
		} elseif ($field->type === T::Enumeration) {
			$max = 0;
			$class = $field->extra;
			foreach ($class::getTypeOptions() as $option) {
				$max = max($max, strlen($option));
			}
			$field->type = 'varchar(' . $max . ')';
			$field->extra = '';
		}
		if (in_array($name, static::getPrimaryKey())) {
			$field->key = 'PRI';
		} else {
			$field->key = '';
		}
		return $field;
	}

	/**
	 * Check for differences in persistent fields.
	 * 
	 * @unsupported INDEX check; FOREIGN KEY check;
	 */
	public static function checkTable() {
		$orm = get_called_class();
		$fields = array();
		foreach (static::$persistent_fields as $name => $definition) {
			$fields[$name] = self::makePersistentField($name, $definition);
		}
		try {
			$database_fields = group_by_distinct('field', DatabaseAdmin::instance()->sqlDescribeTable(static::getTableName()));
		} catch (Exception $e) {
			if (endsWith($e->getMessage(), static::getTableName() . "' doesn't exist")) {
				DatabaseAdmin::instance()->sqlCreateTable(static::getTableName(), $fields, static::getPrimaryKey());
				return;
			} else {
				throw $e; // rethrow to controller
			}
		}
		// Rename fields
		if (property_exists($orm, 'rename_fields')) {
			foreach (static::$rename_fields as $oldname => $newname) {
				if (property_exists($orm, $newname)) {
					DatabaseAdmin::instance()->sqlChangeField(static::getTableName(), $fields[$newname], $oldname);
				}
			}
		}
		$previous_field = null;
		foreach (static::$persistent_fields as $name => $definition) {
			// Add missing persistent fields
			if (!array_key_exists($name, $database_fields)) {
				if (!(property_exists($orm, 'rename_fields') AND in_array($name, static::$rename_fields))) {
					DatabaseAdmin::instance()->sqlAddField(static::getTableName(), $fields[$name], $previous_field);
				}
			} else {
				// Check exisiting persistent fields for differences
				$diff = false;
				if ($fields[$name]->type !== $database_fields[$name]->type) {
					$diff = true;
				}
				if ($fields[$name]->null !== $database_fields[$name]->null) {
					$diff = true;
				}
				// Cast database value if default value is defined
				if ($fields[$name]->default !== null) {
					if ($definition[0] === T::Boolean) {
						$database_fields[$name]->default = (boolean) $database_fields[$name]->default;
					} elseif ($definition[0] === T::Integer) {
						$database_fields[$name]->default = (int) $database_fields[$name]->default;
					} elseif ($definition[0] === T::Float) {
						$database_fields[$name]->default = (float) $database_fields[$name]->default;
					}
				}
				if ($fields[$name]->default !== $database_fields[$name]->default) {
					$diff = true;
				}
				if ($fields[$name]->extra !== $database_fields[$name]->extra) {
					$diff = true;
				}
				if ($fields[$name]->key !== $database_fields[$name]->key AND ! ($fields[$name]->key === '' AND $database_fields[$name]->key === 'MUL')) {
					$diff = true;
				}
				if ($diff) {
					DatabaseAdmin::instance()->sqlChangeField(static::getTableName(), $fields[$name]);
				}
			}
			$previous_field = $name;
		}
		// Remove non-persistent fields
		foreach ($database_fields as $name => $field) {
			if (!array_key_exists($name, static::$persistent_fields) AND ! (property_exists($orm, 'rename_fields') AND array_key_exists($name, static::$rename_fields))) {
				DatabaseAdmin::instance()->sqlDeleteField(static::getTableName(), $field);
			}
		}
	}

}

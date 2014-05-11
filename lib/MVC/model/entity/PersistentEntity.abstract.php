<?php

require_once 'MVC/model/entity/PersistentEnum.interface.php';
require_once 'MVC/model/entity/PersistentType.enum.php';
require_once 'MVC/model/entity/PersistentField.class.php';

/**
 * PersistentEntity.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class PersistentEntity {

	/**
	 * Constructor is called late by PDO::FETCH_CLASS (after fields are set).
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
		
	}

	public static function getTableName() {
		return static::$table_name;
	}

	public static function getFields() {
		return array_keys(static::$persistent_fields);
	}

	public static function getPrimaryKeys() {
		return static::$primary_keys;
	}

	/**
	 * Get the fields and their values of this object.
	 * 
	 * @param boolean $primary_keys_only
	 * @return array
	 */
	public function getValues($primary_keys_only = false) {
		$values = array();
		if ($primary_keys_only) {
			$fields = $this->getPrimaryKeys();
		} else {
			$fields = $this->getFields();
		}
		foreach ($fields as $field) {
			$values[$field] = $this->$field;
		}
		if ($primary_keys_only) {
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
			} elseif (defined('DB_CHECK') AND
					$definition[0] === T::Enumeration AND ! in_array($this->$field, $definition[3]::getTypeOptions())
			) {
				debugprint(self::getTableName() . '.' . $field . ' invalid ' . $definition[3] . ' value: ' . $this->$field);
			}
		}
	}

	/* PersistentField */

	public static function getFieldName($field_number) {
		$i = 0;
		foreach (static::$persistent_fields as $name => $definition) {
			if ($i === $field_number) {
				return $name;
			}
			$i++;
		}
		return null;
	}

	public static function getFieldType($field_name) {
		return self::getDefinition($field_name, 0);
	}

	public static function isAllowedNull($field_name) {
		return (boolean) self::getDefinition($field_name, 1);
	}

	public static function getDefaultValue($field_name) {
		return self::getDefinition($field_name, 2);
	}

	public static function getExtraProp($field_name) {
		return self::getDefinition($field_name, 3);
	}

	private static function getDefinition($field_name, $definition_number) {
		if (array_key_exists($definition_number, static::$persistent_fields[$field_name])) {
			return static::$persistent_fields[$field_name][$definition_number];
		}
		return null;
	}

	/**
	 * To compare table description of MySQL.
	 * 
	 * @param string $field_name
	 * @return PersistentField
	 */
	private static function makePersistentField($field_name) {
		$field = new PersistentField();
		$field->field = $field_name;
		$field->type = self::getFieldType($field_name);
		$field->default = self::getDefaultValue($field_name);
		$field->extra = (string) self::getExtraProp($field_name);
		if ($field->type === T::Boolean) {
			$field->type = 'tinyint(1)';
		} elseif ($field->type === T::Integer) {
			$field->type = 'int(11)';
		} elseif ($field->type === T::String) {
			$field->type = 'varchar(255)';
		} elseif ($field->type === T::UID) {
			$field->type = 'varchar(4)';
		} elseif ($field->type === T::Enumeration) {
			$max = 0;
			$class = $field->extra;
			foreach ($class::getTypeOptions() as $option) {
				$max = max($max, strlen($option));
			}
			$field->type = 'varchar(' . $max . ')';
			$field->extra = '';
		}
		if (self::isAllowedNull($field_name)) {
			$field->null = 'YES';
		} else {
			$field->null = 'NO';
		}
		if (in_array($field_name, self::getPrimaryKeys())) {
			$field->key = 'PRI';
		} else {
			$field->key = '';
		}
		return $field;
	}

	/**
	 * Check for differences in persistent fields.
	 * 
	 * @unsupported RENAME field; INDEX check; FOREIGN KEY check;
	 */
	public static function checkTable() {
		try {
			$database_fields = group_by_distinct('field', DatabaseAdmin::instance()->sqlDescribeTable(self::getTableName()));
		} catch (Exception $e) {
			if (endsWith($e->getMessage(), self::getTableName() . "' doesn't exist")) {
				$string = DatabaseAdmin::instance()->sqlCreateTable(self::getTableName(), static::$persistent_fields, self::getPrimaryKeys());
				debugprint($string);
				return;
			} else {
				throw $e; // rethrow to controller
			}
		}
		$fields = array();
		$previous_field = null;
		foreach (static::$persistent_fields as $name => $definition) {
			$fields[$name] = self::makePersistentField($name);
			// Add missing persistent fields
			if (!array_key_exists($name, $database_fields)) {
				$string = DatabaseAdmin::instance()->sqlAddField(self::getTableName(), $fields[$name], $previous_field);
				debugprint($string);
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
					if (self::getFieldType($name) === T::Boolean) {
						$database_fields[$name]->default = (boolean) $database_fields[$name]->default;
					} elseif (self::getFieldType($name) === T::Integer) {
						$database_fields[$name]->default = (int) $database_fields[$name]->default;
					} elseif (self::getFieldType($name) === T::Float) {
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
					$string = DatabaseAdmin::instance()->sqlChangeField(self::getTableName(), $fields[$name]);
					debugprint($string);
				}
			}
			$previous_field = $name;
		}
		// Remove non-persistent fields
		foreach ($database_fields as $name => $field) {
			if (!array_key_exists($name, static::$persistent_fields)) {
				$string = DatabaseAdmin::instance()->sqlDeleteField(self::getTableName(), $field);
				debugprint($string);
			}
		}
	}

}

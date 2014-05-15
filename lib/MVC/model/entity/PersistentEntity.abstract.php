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
					$definition[0] === T::Enumeration AND ! in_array($this->$field, $definition[2]::getTypeOptions())
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
		} elseif ($field->type === T::Enumeration) {
			$max = 0;
			$class = $field->extra;
			foreach ($class::getTypeOptions() as $option) {
				$max = max($max, strlen($option));
			}
			$field->type = 'varchar(' . $max . ')';
			$field->extra = '';
		}
		if (in_array($name, static::getPrimaryKeys())) {
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
		$orm = get_called_class();
		$fields = array();
		foreach (static::$persistent_fields as $name => $definition) {
			$fields[$name] = self::makePersistentField($name, $definition);
		}
		try {
			$database_fields = group_by_distinct('field', DatabaseAdmin::instance()->sqlDescribeTable(static::getTableName()));
		} catch (Exception $e) {
			if (endsWith($e->getMessage(), static::getTableName() . "' doesn't exist")) {
				$string = DatabaseAdmin::instance()->sqlCreateTable(static::getTableName(), $fields, static::getPrimaryKeys());
				debugprint($string);
				return;
			} else {
				throw $e; // rethrow to controller
			}
		}
		// Rename fields
		if (property_exists($orm, 'rename_fields')) {
			foreach (static::$rename_fields as $oldname => $newname) {
				$string = DatabaseAdmin::instance()->sqlChangeField(static::getTableName(), $fields[$newname], $oldname);
				debugprint($string);
			}
		}
		$previous_field = null;
		foreach (static::$persistent_fields as $name => $definition) {
			// Add missing persistent fields
			if (!array_key_exists($name, $database_fields)) {
				$string = DatabaseAdmin::instance()->sqlAddField(static::getTableName(), $fields[$name], $previous_field);
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
					$string = DatabaseAdmin::instance()->sqlChangeField(static::getTableName(), $fields[$name]);
					debugprint($string);
				}
			}
			$previous_field = $name;
		}
		// Remove non-persistent fields
		foreach ($database_fields as $name => $field) {
			if (!array_key_exists($name, static::$persistent_fields)) {
				$string = DatabaseAdmin::instance()->sqlDeleteField(static::getTableName(), $field);
				debugprint($string);
			}
		}
	}

}

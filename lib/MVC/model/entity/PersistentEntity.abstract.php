<?php

require_once 'MVC/model/entity/PersistentEnum.interface.php';
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

	public static function getPrimaryKey() {
		return static::$primary_key;
	}

	public static function getFields() {
		return array_keys(static::$persistent_fields);
	}

	public static function getDefaultValue($field_name) {
		if (array_key_exists(3, static::$persistent_fields[$field_name])) {
			return static::$persistent_fields[$field_name][3];
		}
		return null;
	}

	public static function getMaxLength($field_name) {
		if (array_key_exists(1, static::$persistent_fields[$field_name])) {
			return static::$persistent_fields[$field_name][1];
		}
		return null;
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
			if (defined('DB_CHECK') AND $this->$field === null AND ! (array_key_exists(2, $definition) AND $definition[2] === true)) {
				throw new Exception(self::getTableName() . '.' . $field . ' is not allowed to be NULL');
			}
			if ($definition[0] === 'int') {
				$this->$field = (int) $this->$field;
			} elseif ($definition[0] === 'boolean') {
				$this->$field = (boolean) $this->$field;
			} elseif ($definition[0] === 'enum' AND ! in_array($this->$field, $definition[1]::values())) {
				debugprint(self::getTableName() . '.' . $field . ' invalid ' . $definition[1] . ' value: ' . $this->$field);
			}
		}
	}

	private static function makePersistentField($name, array $definition) {
		$field = new PersistentField();
		$field->field = $name;
		$field->default = self::getDefaultValue($name);
		if ($definition[0] === 'enum') {
			$field->type = 'varchar(' . $definition[1]::getMaxLenght() . ')';
		} else {
			$field->type = $definition[0];
			if ($definition[0] === 'varchar' OR $definition[0] === 'int') {
				$field->type .= '(' . $definition[1] . ')';
			}
		}
		if (array_key_exists(2, $definition) AND $definition[2] === true) {
			$field->null = 'YES';
		} else {
			$field->null = 'NO';
		}
		if (array_key_exists(4, $definition)) {
			$field->extra = $definition[4];
		} else {
			$field->extra = '';
		}
		if (in_array($name, self::getPrimaryKey())) {
			$field->key = 'PRI';
		} else {
			$field->key = '';
		}
		return $field;
	}

	/**
	 * Check for differences in persistent fields.
	 * 
	 * @important Not supported: RENAMING of field and INDEX and FOREIGN KEY check
	 */
	public static function checkTable() {
		try {
			$database_fields = group_by_distinct('field', DatabaseAdmin::instance()->sqlDescribeTable(self::getTableName()));
		} catch (Exception $e) {
			if (endsWith($e->getMessage(), self::getTableName() . "' doesn't exist")) {
				$string = DatabaseAdmin::instance()->sqlCreateTable(self::getTableName(), static::$persistent_fields, self::getPrimaryKey());
				debugprint($string);
				return;
			} else {
				throw $e; // rethrow to controller
			}
		}
		$fields = array();
		$previous_field = null;
		foreach (static::$persistent_fields as $name => $definition) {
			$fields[$name] = self::makePersistentField($name, $definition);
			// Add missing persistent fields
			if (!array_key_exists($name, $database_fields)) {
				$string = DatabaseAdmin::instance()->sqlAddField(self::getTableName(), $fields[$name], $previous_field);
				debugprint($string);
			} else {
				// Check exisiting persistent fields for differences
				$diff = false;
				if ($fields[$name]->type !== $database_fields[$name]->type AND ! ($fields[$name]->type === 'boolean' AND $database_fields[$name]->type === 'tinyint(1)')) {
					$diff = true;
				}
				if ($fields[$name]->null !== $database_fields[$name]->null) {
					$diff = true;
				}
				if ($fields[$name]->default !== null) {
					if ($definition[0] === 'boolean') {
						$database_fields[$name]->default = (boolean) $database_fields[$name]->default;
					} elseif ($definition[0] === 'int') {
						$database_fields[$name]->default = (int) $database_fields[$name]->default;
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

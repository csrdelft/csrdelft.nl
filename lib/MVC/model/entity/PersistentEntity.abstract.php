<?php

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
	public function __construct() {
		$this->castValues();
	}

	/**
	 * Static constructor is called (by inheritance) first and only from PersistenceModel.
	 */
	public static function __constructStatic() {
		self::checkTable();
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
			if ($definition[0] === 'int') {
				$this->$field = (int) $this->$field;
			} elseif ($definition[0] === 'boolean') {
				$this->$field = (boolean) $this->$field;
			} elseif ($this->$field === null AND ! // Is field allowed to be null
					(array_key_exists(2, $definition) AND $definition[2] === true)
			) {
				// Set default value if not null
				if (array_key_exists(3, $definition) AND $definition[3] !== null) {
					$this->$field = $definition[3];
				} else {
					$this->$field = '';
				}
			}
		}
	}

	private static function makePersistentField($name, array $definition) {
		$field = new PersistentField();
		$field->field = $name;
		$field->type = $definition[0];
		if ($definition[0] === 'varchar' OR $definition[0] === 'int') {
			$field->type .= '(' . $definition[1] . ')';
		}
		if (array_key_exists(2, $definition) AND $definition[2] === true) {
			$field->null = 'YES';
		} else {
			$field->null = 'NO';
		}
		$field->default = self::getDefaultValue($name);
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
	 * Create database table.
	 * 
	 * @return string SQL query
	 */
	public static function createTable() {
		$string = DatabaseAdmin::instance()->sqlCreateTable(self::getTableName(), static::$persistent_fields, self::getPrimaryKey());
		debugprint($string);
	}

	/**
	 * Check for differences in persistent fields.
	 * 
	 * @param boolean modify
	 * @retun string SQL query
	 */
	public static function checkTable($modify = false) {
		try {
			$database_fields = array_key_property('field', DatabaseAdmin::instance()->sqlDescribeTable(self::getTableName()));
		} catch (Exception $e) {
			if (endsWith($e->getMessage(), self::getTableName() . "' doesn't exist")) {
				if ($modify) {
					self::createTable();
				} else {
					debugprint(self::getTableName() . ' TABLE DOES NOT EXIST');
				}
			} else {
				debugprint($e->getMessage());
			}
			exit;
		}
		$fields = array();
		$previous_field = null;
		foreach (static::$persistent_fields as $name => $definition) {
			$fields[$name] = self::makePersistentField($name, $definition);
			// Add missing persistent fields
			if (!array_key_exists($name, $database_fields)) {
				if ($modify) {
					$string = DatabaseAdmin::instance()->sqlAddField(self::getTableName(), $fields[$name], $previous_field);
					debugprint($string);
				} else {
					debugprint(self::getTableName() . '.' . $name . ' MISSING FROM DATABASE');
				}
			} else {
				// Check exisiting persistent fields for differences
				if ($fields[$name]->type !== $database_fields[$name]->type AND ! ($fields[$name]->type === 'boolean' AND $database_fields[$name]->type === 'tinyint(1)')) {
					debugprint(self::getTableName() . '.' . $name . ' TYPE: "' . $fields[$name]->type . '" !== "' . $database_fields[$name]->type . '"');
				}
				if ($fields[$name]->null !== $database_fields[$name]->null) {
					debugprint(self::getTableName() . '.' . $name . ' NULL: "' . $fields[$name]->null . '" !== "' . $database_fields[$name]->null . '"');
				}
				if ($fields[$name]->default != $database_fields[$name]->default) {
					debugprint(self::getTableName() . '.' . $name . ' DEFAULT: "' . $fields[$name]->default . '" != "' . $database_fields[$name]->default . '"');
				}
				if ($fields[$name]->extra !== $database_fields[$name]->extra) {
					debugprint(self::getTableName() . '.' . $name . ' EXTRA: "' . $fields[$name]->extra . '" !== "' . $database_fields[$name]->extra . '"');
				}
				if ($fields[$name]->key !== $database_fields[$name]->key AND $modify) { // ignore normally
					debugprint(self::getTableName() . '.' . $name . ' KEY: "' . $fields[$name]->key . '" !== "' . $database_fields[$name]->key . '"');
				}
			}
			$previous_field = $name;
		}
		// Remove non-persistent fields
		foreach ($database_fields as $name => $field) {
			if (!array_key_exists($name, static::$persistent_fields)) {
				if ($modify) {
					$string = DatabaseAdmin::instance()->sqlDeleteField(self::getTableName(), $field);
					debugprint($string);
				} else {
					debugprint(self::getTableName() . '.' . $name . ' UNDEFINED PROPERTY');
				}
			}
		}
	}

}

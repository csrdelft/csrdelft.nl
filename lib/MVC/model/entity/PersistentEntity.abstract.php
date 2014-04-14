<?php

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
		foreach (static::$persistent_fields as $field => $type) {
			if (startsWith($type, 'int')) {
				$this->$field = (int) $this->$field;
			} elseif (startsWith($type, 'boolean')) {
				$this->$field = (boolean) $this->$field;
			} elseif ($this->$field === null AND strpos($type, 'NOT NULL') !== false) {
				$this->$field = '';
			}
		}
	}

	/**
	 * Create database table.
	 * 
	 * @return string SQL query
	 */
	public function createTable() {
		return Database::instance()->sqlCreateTable($this->getTableName(), static::$persistent_fields, $this->getPrimaryKey());
	}

}

<?php

/**
 * PersistentEntity.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class PersistentEntity {

	protected static $table_name;
	protected static $primary_key;
	protected static $persistent_fields;

	/**
	 * Constructor is called late by PDO::FETCH_CLASS (after fields are set).
	 */
	public function __construct() {
		$this->castValues();
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
			} else if (startsWith($type, 'tinyint')) {
				$this->$field = (boolean) $this->$field;
			} else if ($this->$field === null AND strpos($type, 'NOT NULL')) {
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

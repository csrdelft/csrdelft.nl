<?php

/**
 * PersistentEntity.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class PersistentEntity {

	protected static $table_name;
	protected static $persistent_fields;
	protected static $primary_key;

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

	public function getValues() {
		$values = array();
		foreach ($this->getFields() as $field) {
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
			}
		}
	}

}

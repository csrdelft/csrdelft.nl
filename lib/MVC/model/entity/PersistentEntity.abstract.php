<?php

/**
 * PersistentEntity.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class PersistentEntity {

	public static $table_name;
	public static $persistent_fields;
	public static $primary_key;

	public function getPersistingValues() {
		$fields = $this::$persistent_fields;
		foreach ($fields as $key => $value) {
			$fields[$key] = $this->$key;
		}
		return $fields;
	}

	/**
	 * Get field and cast type.
	 * @param string $field name of persistent field
	 * @return type of field
	 */
	public function get($field, $cast = true) {
		$value = $this->$field;
		if ($cast) {
			$value = $this->cast($field, $value);
		}
		return $value;
	}

	/**
	 * Set field value and cast type.
	 * @param string $field
	 * @param type $value
	 */
	public function set($field, $value, $cast = false) {
		if ($cast) {
			$value = $this->cast($field, $value);
		}
		$this->$field = $value;
	}

	protected function cast($field, $value) {
		if (is_string($value)) {
			if (startsWith(self::$persistent_fields[$field], 'varchar') OR startsWith(self::$persistent_fields[$field], 'text')) {
				return $value;
			} elseif (startsWith(self::$persistent_fields[$field], 'int')) {
				return (int) $value;
			} elseif (startsWith(self::$persistent_fields[$field], 'tinyint')) {
				return (bool) $value;
			} elseif (startwWith(self::$persistent_fields[$field], 'datetime')) {
				return new DateTime($value);
			}
		}
		return $value;
	}

}

?>
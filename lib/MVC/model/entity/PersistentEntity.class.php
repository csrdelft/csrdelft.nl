<?php

/**
 * PersistentEntity.class.php
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

}

?>
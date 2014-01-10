<?php

/**
 * PersistentEntity.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
abstract class PersistentEntity {

	public abstract static function getPrimaryKey();

	public abstract static function getPersistentFields();

	public function getPersistingValues() {
		$fields = self::getPersistingFields();
		foreach ($fields as $key => $value) {
			$fields[$key] = $this->$key;
		}
		return $fields;
	}

}

?>
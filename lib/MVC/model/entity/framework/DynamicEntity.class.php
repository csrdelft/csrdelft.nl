<?php

/**
 * DynamicEntity.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Dynamic entities are defined by the table structure instead of the other way around. 
 * Conversion: only define the new class and dynamicly load the old class definition.
 * 
 */
class DynamicEntity extends PersistentEntity {

	/**
	 * The definition of this entity
	 * @var DynamicEntityDefinition
	 */
	public $definition;

	public function __construct($cast = false, array $attr_retrieved = null, DynamicEntityDefinition $definition = null) {
		$this->definition = $definition;
		parent::__construct($cast, $attr_retrieved);
	}

	public function getTableName() {
		return $this->definition->table_name;
	}

	/**
	 * Get all attribute names.
	 * 
	 * @return array
	 */
	public function getAttributes() {
		return array_keys($this->definition->persistent_attributes);
	}

	public function getAttributeDefinition($attribute_name) {
		return $this->definition->persistent_attributes[$attribute_name];
	}

	public function getPrimaryKey() {
		return $this->definition->primary_key;
	}

	public function __set($attribute, $value) {
		$this->$attribute = $value;
	}

	public function __get($attribute) {
		if (property_exists(get_class($this), $attribute)) {
			return $this->$attribute;
		}
		return null;
	}

	public function __isset($attribute) {
		return $this->__get($attribute) !== null;
	}

	public function __unset($attribute) {
		if ($this->__isset($attribute)) {
			unset($this->$attribute);
		}
	}

}

<?php

require_once 'model/framework/PersistenceModel.abstract.php';
require_once 'model/framework/DatabaseAdmin.singleton.php';

/**
 * DynamicEntityModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Defines the DynamicEntity class by the DynamicEntityDefinition.
 * Factory pattern instead of singleton, so ::instance() won't work!
 * 
 */
class DynamicEntityModel extends PersistenceModel {

	const ORM = 'DynamicEntity';
	const DIR = 'framework/';

	/**
	 * Factory pattern instead of singleton.
	 * @see ::makeModel()
	 */
	public static function instance() {
		throw new Exception('Use makeModel');
	}

	public static function makeModel($table_name) {
		parent::__static();
		return new static($table_name);
	}

	/**
	 * Definition of the DynamicEntity
	 * @var DynamicEntityDefinition
	 */
	private $definition;

	/**
	 * Override the constructor of PersistentModel and create DynamicEntityDefinition from table structure.
	 * 
	 * @param string $table_name
	 */
	protected function __construct($table_name) {
		$this->definition = new DynamicEntityDefinition();
		$this->definition->table_name = $table_name;
		foreach (DatabaseAdmin::instance()->sqlDescribeTable($this->definition->table_name) as $attribute) {
			$this->definition->persistent_attributes[$attribute->field] = PersistentAttribute::makeDefinition($attribute);
			if ($attribute->key === 'PRI') {
				$this->definition->primary_key[] = $attribute->field;
			}
		}
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
		return array_values($this->definition->primary_key);
	}

	protected function retrieveByPrimaryKey(array $primary_key_values) {
		$entity = parent::retrieveByPrimaryKey($primary_key_values);
		if ($entity) {
			$entity->definition = $this->definition;
		}
		return $entity;
	}

	public function find($criteria = null, array $criteria_params = array(), $groupby = null, $orderby = null, $limit = null, $start = 0) {
		$result = parent::find($criteria, $criteria_params, $groupby, $orderby, $limit, $start);
		if ($result) {
			$result->setFetchMode(PDO::FETCH_CLASS, static::ORM, array($cast = true, null, $this->definition));
		}
		return $result;
	}

	public function findSparse(array $attributes, $criteria = null, array $criteria_params = array(), $groupby = null, $orderby = null, $limit = null, $start = 0) {
		$result = parent::findSparse($attributes, $criteria, $criteria_params, $groupby, $orderby, $limit, $start);
		if ($result) {
			$result->setFetchMode(PDO::FETCH_CLASS, static::ORM, array($cast = true, $attributes, $this->definition));
		}
		return $result;
	}

}
